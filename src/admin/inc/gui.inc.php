<?php
use RescueMe\Mobile;

/**
     * Admin GUI functions
     *
     * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 01. March 2014
     *
     * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
     */

    function insert_trace_menu($user, $id='trace', $output=true) {

        ob_start();
        require(ADMIN_PATH . "gui/trace.menu.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }


    function insert_system_menu($user, $id='system', $output=true) {

        ob_start();
        require(ADMIN_PATH . "gui/system.menu.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }


    /**
     * Insert trace progress bar
     * @param Mobile $mobile
     * @param bool $collapsed
     * @param bool $output
     * @return string
     */
    function insert_trace_progress($mobile, $collapsed = false, $output=true) {
        
        $timeout = (time() - strtotime($mobile->alerted)) > 3*60*60*1000;

        $trace['alerted']['state'] = 'pass';
        $trace['alerted']['time'] = format_since($mobile->alerted);
        $trace['alerted']['timestamp'] = format_tz($mobile->alerted);
        $trace['alerted']['tooltip'] = T_('Trace started');
        if($mobile->sms_sent !== null) {
            $trace['sent']['state'] = 'pass';
            $trace['sent']['time'] = format_since($mobile->sms_sent);
            $trace['sent']['timestamp'] = format_tz($mobile->sms_sent);
            $trace['sent']['tooltip'] = T_('SMS sent');
        } else {
            $trace['sent']['state'] = 'fail';
            $trace['sent']['time'] = T_('Unknown');
            $trace['sent']['timestamp'] = '';
            $trace['sent']['tooltip'] = T_('SMS not sent').'. '.T_('Check log');
        }            
        if($mobile->sms_delivered !== null) {
            $trace['delivered']['state'] = 'pass';
            $trace['delivered']['time'] = format_since($mobile->sms_delivered);
            $trace['delivered']['timestamp'] = format_tz($mobile->sms_delivered);
            $trace['delivered']['tooltip'] = T_('SMS received');
        } else {

            $state = '';
            if($mobile->responded !== null || $mobile->sms_sent !== null) {
                $state = 'warning';
            } elseif($timeout) {
                $state = 'fail'; 
            } 
            $trace['delivered']['state'] = $state;
            $trace['delivered']['time'] = T_('Unknown');
            $trace['delivered']['timestamp'] = '';
            switch($state)
            {
                case 'warning':
                    $trace['delivered']['tooltip'] = 
                        T_('SMS is delivered, but delivery report from SMS provider not received');
                    break;
                case 'fail':
                    $trace['delivered']['tooltip'] = 
                        sprintf(T_('SMS not delivered after %1$d hours'),3) . '. ' . T_('The phone may be out of power or coverage.');
                    break;
                default:
                    $trace['delivered']['tooltip'] = 
                        T_('SMS probably not delivered') . '. ' . T_('The phone may be out of power or coverage.');
                    break;
            }
            if($state) {
            } else {
            }
        }            
        if($mobile->responded !== null) {
            // Get number of errors reported from mobile
            if(($errors = $mobile->getErrors(true)) === FALSE) {
                $trace['responded']['state'] = 'pass';
                $trace['responded']['time'] = format_since($mobile->responded);
                $trace['responded']['timestamp'] = format_tz($mobile->responded);
                $trace['responded']['tooltip'] = T_('Trace script downloaded');
            } else {
                $trace['responded']['state'] = 'fail';
                $trace['responded']['time'] = format_since($mobile->responded);
                $trace['responded']['timestamp'] = format_tz($mobile->responded);
                $header = T_('Trace script has reported errors');
                $items = array();
                foreach($errors as $number => $count) {
                    switch($number) {
                        case 1:
                            $items[] = sprintf(T_('Permission denied %s times.'), $count);
                            break;
                        case 2:
                            $items[] = sprintf(T_('Location unavailable %s times.'), $count);
                            break;
                        case 3:
                            $items[] = sprintf(T_('Location timeout %s times.'), $count);
                            break;
                    }
                }
                $tooltip = sprintf("%s %s",$header, implode('\\n',$items));
                $trace['responded']['tooltip'] = $tooltip;
            }

        } else {
            $trace['responded']['state'] = '';
            $trace['responded']['time'] = T_('Unknown');
            $trace['responded']['timestamp'] = '';
            $trace['responded']['tooltip'] = T_('Trace script not downloaded') . '. ' .
                T_('The phone may be out of power, out of coverage, support for localization can be turned off or not possible, or the user chose not to share their location with you.');
        }            
        if($mobile->last_pos->timestamp>-1) {
            $trace['located']['state'] = 'pass';
            $trace['located']['time'] = format_since($mobile->last_pos->timestamp);
            $trace['located']['timestamp'] = format_tz($mobile->last_pos->timestamp);
            $trace['located']['tooltip'] = T_('Mobile located');
        } else {

            if($mobile->responded !== null || $mobile->sms_sent !== null) {
                $trace['located']['state'] = '';
            } elseif($timeout) {
                $trace['located']['state'] = 'fail'; 
            } 
            
            if($mobile->responded !== null) {
                $trace['located']['tooltip'] = T_('Trace script is downloaded, but not location is received') . '. ' .
                    T_('The phone may be out of power, out of coverage, support for localization can be turned off or not possible, or the user chose not to share their location with you.');
            } else {
                $trace['located']['tooltip'] = T_('Trace script not downloaded') . '. ' .
                    T_('The phone may be out of power, out of coverage, support for localization can be turned off or not possible, or the user chose not to share their location with you.');
            }
            $trace['located']['time'] = T_('Unknown');
            $trace['located']['timestamp'] = '';
        }
        
        ob_start();
        require(ADMIN_PATH . "gui/trace.progress.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
