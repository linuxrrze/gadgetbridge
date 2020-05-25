<?php
namespace OCA\GadgetBridge\Devices;

use Carbon\Carbon;
use OCA\GadgetBridge\Database;
use OCA\GadgetBridge\Activity\ActivityKind;
use OCA\GadgetBridge\Devices\DeviceData;
use OCA\GadgetBridge\Activity\ActivityAmount;
use OCA\GadgetBridge\Activity\ActivityAmounts;


abstract class Device
{
    //TODO: A Better place to put these?
    const TYPE_UNSET = -1;
    const TYPE_NO_CHANGE = 0;
    const TYPE_ACTIVITY = 1;
    const TYPE_RUNNING = 2;
    const TYPE_NONWEAR = 3;
    const TYPE_CHARGING = 6;
    const TYPE_LIGHT_SLEEP = 9;
    const TYPE_IGNORE = 10;
    const TYPE_DEEP_SLEEP = 11;
    const TYPE_WAKE_UP = 12;

    public DeviceData $data;
    protected Database $database;

    public function __construct(array $device, $database)
    {
        $this->data = new DeviceData($device);
        $this->database = $database;
    }

    abstract public function getSamples(Carbon $start = null, Carbon $end = null);

    public function getSamplesForDay(Carbon $timeInDay)
    {
        $beginOfDay = $timeInDay->startOfDay();
        $endOfDay = $beginOfDay->addDay();

        return $this->getSamples($beginOfDay, $endOfDay);
    }

    public function formatSamplesForChartJs(Array $samples): Array
    {
        $steps = array_column($samples, 'STEPS');
        $timestamps = array_column($samples, 'TIMESTAMP');
        $kinds = array_column($samples, 'RAW_KIND');
        $activityColors = array_column($samples, 'ACTIVITY_COLOR');
        $heartRates = array_column($samples, 'HEART_RATE');
        return [
            'STEPS' => $steps,
            'TIMESTAMPS' => $timestamps,
            'KINDS' => $kinds,
            'ACTIVITY_COLORS' => $activityColors,
            'HEART_RATES' => $heartRates
        ];
    }

    public function calculateActivityAmounts(array $samples)
    {
        $deepSleep = new ActivityAmount(ActivityKind::TYPE_DEEP_SLEEP);
        $lightSleep = new ActivityAmount(ActivityKind::TYPE_LIGHT_SLEEP);
        $notWorn = new ActivityAmount(ActivityKind::TYPE_NOT_WORN);
        $activity = new ActivityAmount(ActivityKind::TYPE_ACTIVITY);

        $previousAmount = null;
        $previousSample = null;
        $maxSpeed = 0;
        foreach($samples as $sample) {
            // $refAmount = null; // A reference to one of the above amount collections.
            // var_dump($sample);
            // var_dump($sample['RAW_KIND']);
            switch ($sample['RAW_KIND']) {
                case ActivityKind::TYPE_DEEP_SLEEP:
                    $refType = 'deepSleep';
                    break;
                case ActivityKind::TYPE_LIGHT_SLEEP:
                    $refType = 'lightSleep';
                    break;
                case ActivityKind::TYPE_NOT_WORN:
                    $refType = 'notWorn';
                    break;
                case ActivityKind::TYPE_ACTIVITY:
                default:
                    $refType = 'activity';
                    break;
            }
            $refAmount = $$refType;
            // var_dump($refAmount);

            $steps = $sample['STEPS'];
            if ($steps > 0) {
                $refAmount->addSteps($steps);
            }

            if (!is_null($previousSample)) {
                $timeDiff = Carbon::createFromTimestampMs($sample['TIMESTAMP'])->diffInSeconds(Carbon::createFromTimestampMs($previousSample['TIMESTAMP']));

                if($previousSample['RAW_KIND'] === $sample['RAW_KIND']) {
                    // The activity continues
                    $refAmount->addSeconds($timeDiff);
                } else {
                    // Split the time between the two evenly
                    $$previousAmount->addSeconds($timeDiff/2); // I'm not sure if this will work in PHP.  Maybe?
                    $refAmount->addSeconds($timeDiff/2);
                }

                if ( $steps > 0 && $sample['RAW_KIND'] == ActivityKind::TYPE_ACTIVITY) {
                    if ($steps > $maxSpeed) {
                        $maxSpeed = $steps;
                    }
// TODO: Port if this is useful.  Not sure where it was used in the java code, stats was a protected property
//                     if (!stats.containsKey(steps)) {
// //                        LOG.debug("Adding: " + steps);
//                         stats.put(steps, timeDifference);
//                     } else {
//                         long time = stats.get(steps);
// //                        LOG.debug("Updating: " + steps + " " + timeDifference + time);
//                         stats.put(steps, timeDifference + time);
//                     }
                }
            }

            $refAmount->setStartDate($sample['TIMESTAMP']);
            $refAmount->setEndDate($sample['TIMESTAMP']);

            $previousAmount = $refType;
            // var_dump($previousAmount);
            $previousSample = $sample; 
            // var_dump($previousSample);
        }
        unset($sample);

        // var_dump($lightSleep);

        $results = new ActivityAmounts();
        $results->addAmount($deepSleep);
        $results->addAmount($lightSleep);
        $results->addAmount($activity);

        $results->calculatePercentages();

        return $results;
    }
}
