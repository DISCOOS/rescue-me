<?php

    $defined = defined('DOMAIN_TRACE');

    if($defined === FALSE) {

        define('DOMAIN_TRACE','trace');

        /**
         * @return array
         */
        function get_messages() {

            /*
             * Get messages in domain 'trace'
             *
             * NOTE: We do not use i18next.js here because of the overhead it introduces!
             */

            $msg = array();
            $msg[0] = T_('Geolocation not supported');
            $msg[1] = T_('Found location with &#177;{0} m accuracy');
            $msg[2] = T_('Location is old, check if GPS is on!');
            $msg[3] = T_('Waiting for higher accuracy...');
            $msg[4] = T_('Turn on permission to access location data!');
            $msg[5] = T_('Location is unavailable.');
            $msg[6] = T_('Please approve access to location faster!');
            $msg[7] = T_('Unknown error');
            $msg[8] = T_('Location: {0}');
            $msg[9] = T_('Calculating');
            $msg[10] = T_('Location not sent, check data connection!');
            $msg[11] = T_('Send location as');
            $msg[12] = T_('Location not found');
            $msg[13] = T_('Update');
            if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')) {
                $msg[14] = T_('Go to Settings -> General -> Privacy -> Location services -> On.');
                $msg[15] = T_('Also, scroll down and make sure it is set to <b>On</b> for Safari as well.');
                $msg[16] = T_('Then, click <b>Update</b> below.');
            }
            return $msg;
        }

    }

    return $defined;
