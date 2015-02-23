<?php

    $defined = defined('DOMAIN_SMS');

    if($defined === FALSE) {

        define('DOMAIN_SMS','sms');

//        define('ALERT_SMS_TRACE', 'We are searching for you! Click on this link so we can locate you: %LINK%');
//        define('ALERT_SMS_NOT_SENT', 'Warning: SMS not sent to #m_name');
//        define('ALERT_SMS_2', 'If you have GPS on your phone, we recommend that you enable this. In most cases you will find this under Settings -> General, or Settings -> Location.');
//        define('ALERT_SMS_LOCATION_UPDATE', 'Received location of #m_name: #pos (+/-#acc m)!' . ' '. ADMIN_TRACE_URL);




    }

    return $defined;