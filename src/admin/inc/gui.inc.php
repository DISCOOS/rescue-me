<?php

    /**
	 * Admin GUI functions
	 * 
	 * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation}  
	 *
     * @since 01. March 2014
	 * 
	 * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
	 */
    
    function insert_trace_bar($missing, $collapsed = false, $output=true) {
        
        $timeout = (time() - strtotime($missing->reported)) > 3*60*60*1000;
        
        $trace['alerted']['state'] = 'pass';
        $trace['alerted']['time'] = format_since($missing->reported);
        $trace['alerted']['timestamp'] = $missing->reported.\RescueMe\TimeZone::getOffset();
        $trace['alerted']['tooltip'] = TRACE_STARTED;
        if($missing->sms_sent !== null) {
            $trace['sent']['state'] = 'pass';
            $trace['sent']['time'] = format_since($missing->sms_sent);
            $trace['sent']['timestamp'] = $missing->sms_sent.\RescueMe\TimeZone::getOffset();
            $trace['sent']['tooltip'] = SMS_SENT;
        } else {
            $trace['sent']['state'] = 'fail';
            $trace['sent']['time'] = UNKNOWN;
            $trace['sent']['timestamp'] = '';
            $trace['sent']['tooltip'] = SMS_NOT_SENT.'. '.CHECK_LOG;
        }            
        if($missing->sms_delivery !== null) {
            $trace['delivered']['state'] = 'pass';
            $trace['delivered']['time'] = format_since($missing->sms_delivery);
            $trace['delivered']['timestamp'] = $missing->sms_delivery.\RescueMe\TimeZone::getOffset();
            $trace['delivered']['tooltip'] = SMS_RECEIVED;
        } else {
            
            $state = '';
            if($missing->answered !== null || $missing->sms_sent !== null) {
                $state = 'warning';
            } elseif($timeout) {
                $state = 'fail'; 
            } 
            $trace['delivered']['state'] = $state;
            $trace['delivered']['time'] = UNKNOWN;
            $trace['delivered']['timestamp'] = '';
            switch($state)
            {
                case 'warning':
                    $trace['delivered']['tooltip'] = 
                        SMS_DELIVERED_BUT_DELIVERY_REPOST_NOT_RECEIVED;                    
                    break;
                case 'fail':
                    $trace['delivered']['tooltip'] = 
                        sprintf(SMS_NOT_DELIVERED_AFTER_D_HOURS,3) . ' ' . MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE;
                    break;
                default:
                    $trace['delivered']['tooltip'] = 
                        SMS_PROBABLY_NOT_DELIVERED . ' ' . MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE;
                    break;
            }
            if($state) {
            } else {
            }
        }            
        if($missing->answered !== null) {
            $trace['response']['state'] = 'pass';
            $trace['response']['time'] = format_since($missing->answered);
            $trace['response']['timestamp'] = $missing->answered.\RescueMe\TimeZone::getOffset();
            $trace['response']['tooltip'] = TRACE_SCRIPT_DOWNLOADED;
        } else {
            $trace['response']['state'] = '';
            $trace['response']['time'] = UNKNOWN;
            $trace['response']['timestamp'] = '';
            $trace['response']['tooltip'] = TRACE_SCRIPT_NOT_DOWNLOADED . ' ' . 
                MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE_LONG_DESCRIPTION;
        }            
        if($missing->last_pos->timestamp>-1) {
            $trace['located']['state'] = 'pass';
            $trace['located']['time'] = format_since($missing->last_pos->timestamp);
            $trace['located']['timestamp'] = $missing->last_pos->timestamp.\RescueMe\TimeZone::getOffset();
            $trace['located']['tooltip'] = MOBILE_LOCATED;
        } else {

            if($missing->answered !== null || $missing->sms_sent !== null) {
                $trace['located']['state'] = '';
            } elseif($timeout) {
                $trace['located']['state'] = 'fail'; 
            } 
            
            if($missing->answered !== null) {
                $trace['located']['tooltip'] = TRACE_SCRIPT_DOWNLOADED_BUT_LOCATION_NOT_RECEIVED . ' ' . 
                    MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE_LONG_DESCRIPTION;
            } else {
                $trace['located']['tooltip'] = TRACE_SCRIPT_NOT_DOWNLOADED . ' ' . 
                    MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE_LONG_DESCRIPTION;
            }
            $trace['located']['time'] = UNKNOWN;            
            $trace['located']['timestamp'] = '';
        }
        
        ob_start();
        require(ADMIN_PATH . "gui/missing.trace.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
