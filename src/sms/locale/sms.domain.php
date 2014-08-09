<?php

    use RescueMe\Locale;
    
    $defined = defined('ALERT_SMS_TRACE');

    if($defined === FALSE) {

        define('ALERT_SMS_TRACE', T_('You are missed! Click on link to show us where you are: %LINK%'));
        define('ALERT_SMS_NOT_SENT', T_('Warning: SMS not sent to #m_name'));
        define('ALERT_SMS_2', T_('If you have GPS on your phone, we recommend that you enable this. ' . 
            'In most cases you will find this under Settings -> General, or Settings -> Location.'));
        define('ALERT_SMS_LOCATION_UPDATE', T_('Received location of #m_name: #pos (+/-#acc m)!') . ' '. ADMIN_TRACE_URL);
        
    }

    return $defined;