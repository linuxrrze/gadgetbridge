<?php

namespace OCA\GadgetBridge\Devices;

class DeviceFactory
{
    public function make($rawDeviceData, $database)
    {
        // We'll use the name to generate a class name matching it
        // This is a quick and dirty copy of Str::studly from laravel/illuminate
        $spacedclassName = ucwords(str_replace(['-', '_'], ' ', $rawDeviceData['NAME']));
        $condensed = "\\OCA\\GadgetBridge\\Devices\\" . str_replace(' ', '', $spacedclassName) . 'Device'; 
        return new $condensed($rawDeviceData, $database); 

    }    
}
