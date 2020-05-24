<?php
namespace OCA\GadgetBridge\Devices;

use DateTime;
use OCA\GadgetBridge\Database;
use OCA\GadgetBridge\Devices\DeviceData;


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

    public $parsedData;
    public function __construct(array $device, $database)
    {
        $this->data = new DeviceData($device);
        $this->database = $database;
    }

    abstract public function getSamples(DateTime $start = null, DateTime $end = null);
}
