<?php

    $defined = defined('ABORTED');

    if($defined === FALSE) {

        define('ABORTED', T_('Aborted'));
        
        define('CLOSED_BY_S1_AT_S2', T_('Closed by %1$s at %2$s'));
        
        define('FAILED_TO_ABORT_TRACE_S', T_('Failed to abort trace %1$s'));
        
        define('GEOLOCATION_NOT_SUPPORTED', T_('Geolocation not supported.'));
        define('FOUND_LOCATION_WITH_D_ACCURACY', T_('Found location with &#177;{0} m accuracy.'));
        define('LOCATION_IS_OLD_CHECK_IF_GPS_IS_ON', T_('Location is old, check if GPS is on!'));
        define('WAITING_FOR_HIGHER_ACCURACY', T_('Waiting for higher accuracy...'));
        define('TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA', T_('Turn on permission to access location data!'));
        define('IOS_TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA', T_('Go to Settings -> General -> Privacy -> Location services -> On.'));
        define('IOS_TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA2', T_('Also, scroll down and make sure it is set to "On" for Safari as well.'));
        define('IOS_TURN_ON_PERMISSION_TO_ACCESS_LOCATION_DATA3', T_('Then, click "Update" below.'));
        define('LOCATION_IS_UNAVAILABLE', T_('Location is unavailable.'));
        define('PLEASE_APPROVE_ACCESS_TO_LOCATION_FASTER', T_('Plese approve access to location faster.'));
        define('UNKNOWN_ERROR', T_('Unknown error.'));
        define('LOCATION_S', T_('Location: {0}'));
        define('LOCATION_NOT_SENT_CHECK_DATA_CONNECTION', T_('Location not sent, check data connection.'));
        define('SEND_LOCATION_AS', T_('Send location as'));
        define('LOCATION_NOT_FOUND', T_('Location not found.'));

        define('IS_ABORTED', sentence(array(IS,ABORTED)));
        
        define('TRACE_S_IS_ABORTED', sentence(array(TRACE_S,IS_ABORTED)));

    }

    return $defined;