# How to add new devices

sqlite3 gadgetbridge.db

sqlite> .tables
ACTIVITY_DESCRIPTION                 NOTIFICATION_FILTER                
ACTIVITY_DESC_TAG_LINK               NOTIFICATION_FILTER_ENTRY          
ALARM                                PEBBLE_HEALTH_ACTIVITY_OVERLAY     
BASE_ACTIVITY_SUMMARY                PEBBLE_HEALTH_ACTIVITY_SAMPLE      
CALENDAR_SYNC_STATE                  PEBBLE_MISFIT_SAMPLE               
DEVICE                               PEBBLE_MORPHEUZ_SAMPLE             
DEVICE_ATTRIBUTES                    TAG                                
HPLUS_HEALTH_ACTIVITY_OVERLAY        TLW64_ACTIVITY_SAMPLE              
HPLUS_HEALTH_ACTIVITY_SAMPLE         USER                               
HYBRID_HRACTIVITY_SAMPLE             USER_ATTRIBUTES                    
ID115_ACTIVITY_SAMPLE                WATCH_XPLUS_ACTIVITY_SAMPLE        
JYOU_ACTIVITY_SAMPLE                 WATCH_XPLUS_HEALTH_ACTIVITY_OVERLAY
MAKIBES_HR3_ACTIVITY_SAMPLE          XWATCH_ACTIVITY_SAMPLE             
MI_BAND_ACTIVITY_SAMPLE              ZE_TIME_ACTIVITY_SAMPLE            
NO1_F1_ACTIVITY_SAMPLE               android_metadata                   

sqlite> SELECT * FROM DEVICE;
1|Mi Smart Band 5|Huami|C0:08:XX:XX:XX:XX|23|V0.44.19.2|

This results in a Device class named "MiSmartBand5Device".
Create file "lib/Devices/MiSmartBand5Device.php" or copy and modify
"lib/Devices/MiBand3Device.php" (adapt class name to match file name).

In function fetchEarliestStartTimestamp() add the integer in column 5
("23") in the array mentioned here:

        // This covers the MI bands that I know about. TODO expand to cover other devices possibly
        if (in_array(intval($this->data->type), [23])) {

