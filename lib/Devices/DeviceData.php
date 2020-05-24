<?php

namespace OCA\GadgetBridge\Devices;


class DeviceData
{
    public $deviceId;
    public $name;
    public $manufacturer;
    public $identifier;
    public $type;
    public $modelId;
    public $beginningDateTimestamp;

    public function __construct(array $device)
    {
        $this->deviceId = $device['_id'];
        $this->name = $device['NAME'];
        $this->manufacturer = $device['MANUFACTURER'];
        $this->identifier = $device['IDENTIFIER'];
        $this->type = $device['TYPE'];
        $this->modelId = $device['MODEL'];
    }
}
