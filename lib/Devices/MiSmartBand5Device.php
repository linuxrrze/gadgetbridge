<?php

namespace OCA\GadgetBridge\Devices;

use DateTime;
use DateInterval;
use OCA\GadgetBridge\ActivityKind;
use OCP\DB\QueryBuilder\IQueryBuilder;


class MiSmartBand5Device extends Device
{

    public function __construct(array $device, $database)
    {
        parent::__construct($device, $database);
        $this->fetchEarliestStartTimestamp();
    }

    public function fetchEarliestStartTimestamp()
    {
        // This covers the MI bands that I know about. TODO expand to cover other devices possibly
        if (in_array(intval($this->data->type), [23])) {
            $newQuery = $this->database->getDatabaseConnection()->getQueryBuilder();

            $newQuery->select('TIMESTAMP')
                ->from('MI_BAND_ACTIVITY_SAMPLE')
                ->where($newQuery->expr()->eq('DEVICE_ID', $newQuery->createNamedParameter($this->data->deviceId)));
            $result = $newQuery->execute();
            $this->data->beginningDateTimestamp = min($newQuery->execute()->fetchAll());
            $result->closeCursor();
        }
    }

    public function getSamples(DateTime $start = null, DateTime $end = null) 
    {
        $beginRange = new DateTime();
        $beginRange->sub(new DateInterval('P10Y'));
        $start = $start ?? $beginRange;
        $end = $end ?? new DateTime();

        $query = $this->database->getDatabaseConnection()->getQueryBuilder();
        $query->automaticTablePrefix(false);
        $query
            ->select('*')
            ->from('MI_BAND_ACTIVITY_SAMPLE')
            ->where($query->expr()->eq('DEVICE_ID', $query->createNamedParameter($this->data->deviceId)))
            ->andWhere($query->expr()->gte('TIMESTAMP', $query->createNamedParameter($start->getTimestamp())))
            ->andWhere($query->expr()->lte('TIMESTAMP', $query->createNamedParameter($end->getTimestamp())))
            ->orderBy('TIMESTAMP', 'ASC');
        $result = $query->execute();
        $samples = $result->fetchAll();
        $result->closeCursor();

		$this->lastValidKind = $this->getLastMiBandActivity($start->getTimestamp());

        $range = $end->diff($start);
        // Lets keep the amount of samples provided to the frontend realistic.
        // A quick and dirty way of doing this: divide all samples by number of days requested.
        $samples = array_values(array_filter($samples, function ($k) use ($range) {
            if ($range->days > 1) {
                return $k % $range->days === 0;
            }
            return true;
        }, ARRAY_FILTER_USE_KEY));

        $samples = array_map([$this, 'postProcessing'], $samples);

        $steps = array_column($samples, 'STEPS');
        $timestamps = array_column($samples, 'TIMESTAMP');
        $kinds = array_column($samples, 'RAW_KIND');
        $activityColors = array_column($samples, 'ACTIVITY_COLOR');
        $heartRates = array_column($samples, 'HEART_RATE');
        $this->parsedData = [
            'STEPS' => $steps,
            'TIMESTAMPS' => $timestamps,
            'KINDS' => $kinds,
            'ACTIVITY_COLORS' => $activityColors,
            'HEART_RATES' => $heartRates
        ];
        return $this->parsedData;
    }
    protected $lastValidKind = self::TYPE_UNSET;
    protected $lastValidHeartRate;

    protected function postProcessing($data)
    {
        if (empty($data)) {
            return $data;
        }

        // We expect MS on the JS side, lets expand timestamp here.
        $data['TIMESTAMP'] *= 1000;

        $rawKind = $data['RAW_KIND'];
        if ($rawKind !== self::TYPE_UNSET) {
            $rawKind &= 0xf;
            $data['RAW_KIND'] = $rawKind;
        }

        switch ($rawKind) {
            case self::TYPE_IGNORE:
            case self::TYPE_NO_CHANGE:
                if ($this->lastValidKind !== self::TYPE_UNSET) {
                    $data['RAW_KIND'] = $this->lastValidKind;
                }
                break;
            default:
                $this->lastValidKind = $data['RAW_KIND'];
                break;
        }

        $data['RAW_KIND'] = $this->normalizeType($data['RAW_KIND']);
        $data['ACTIVITY_COLOR'] = $this->getActivityColor($data['RAW_KIND']);

        // Heartrate Normalization
        $hRate = $data['HEART_RATE'];
        if ($hRate > 20 && $hRate < 255) { // Valid Heartrate
            $this->lastValidHeartRate = $hRate;
        } else if ($hRate > 0) {
            $data['HEART_RATE'] = $this->lastValidHeartRate;
        } else {
            $data['HEART_RATE'] = null;
        }
        $data['RAW_KIND'] *= 10;
        if ($data['RAW_KIND'] < 1) { // Unknown or unmeasured
            $data['STEPS'] = 2;
        } else { // Bound steps between 10 and 250.  Not sure why, old code.
            $data['STEPS'] = min(250, max(10, $data['STEPS']));
        }
        return $data;
    }

    protected function getLastMiBandActivity($beforeTimestamp)
    {
        $query = $this->database->getDatabaseConnection()->getQueryBuilder();
        $query->automaticTablePrefix(false);
        $query
            ->select('RAW_KIND')
            ->from('MI_BAND_ACTIVITY_SAMPLE')
            ->where($query->expr()->eq('DEVICE_ID', $query->createNamedParameter($this->data->deviceId)))
            ->andWhere($query->expr()->lte('TIMESTAMP', $query->createNamedParameter($beforeTimestamp)))
            ->andWhere($query->expr()->notIn('RAW_KIND', $query->createNamedParameter([
                self::TYPE_NO_CHANGE,
                self::TYPE_IGNORE,
                self::TYPE_UNSET,
                16,
                80,
                96,
                112,
            ], IQueryBuilder::PARAM_INT_ARRAY)))
            ->orderBy('TIMESTAMP', 'DESC')
            ->setMaxResults(1);

        $result = $query->execute();
        $step = $result->fetch();
        $result->closeCursor();

        if (!$step) {
            // No data before
            return self::TYPE_UNSET;
        }

        return $step['RAW_KIND'] & 0xf;
    }

    protected function normalizeType($rawType)
    {
        switch ($rawType) {
            case self::TYPE_DEEP_SLEEP:
                return ActivityKind::TYPE_DEEP_SLEEP;
            case self::TYPE_LIGHT_SLEEP:
                return ActivityKind::TYPE_LIGHT_SLEEP;
            case self::TYPE_ACTIVITY:
            case self::TYPE_RUNNING:
            case self::TYPE_WAKE_UP:
                return ActivityKind::TYPE_ACTIVITY;
            case self::TYPE_NONWEAR:
                return ActivityKind::TYPE_NOT_WORN;
            case self::TYPE_CHARGING:
                return ActivityKind::TYPE_NOT_WORN; //I believe it's a safe assumption
            default:
            case self::TYPE_UNSET: // fall through
                return ActivityKind::TYPE_UNKNOWN;
        }
    }

    private function getActivityColor($kind)
    {
        switch ($kind) {
            case ActivityKind::TYPE_ACTIVITY:
                return '#3ADF00';
            case ActivityKind::TYPE_LIGHT_SLEEP:
                return '#2ECCFA';
            case ActivityKind::TYPE_DEEP_SLEEP:
                return '#0040FF';
            case ActivityKind::TYPE_NOT_WORN:
            default:
                return '#AAAAAA';
        }
    }
}
