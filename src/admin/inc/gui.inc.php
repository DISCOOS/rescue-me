<?php

use RescueMe\Finite\Trace\Factory;
use RescueMe\Finite\Trace\State\NotDelivered;
use RescueMe\Finite\Trace\State\Sent;
use RescueMe\Finite\Trace\State\Timeout;
use RescueMe\Manager;
use RescueMe\Mobile;
use RescueMe\Properties;
use RescueMe\SMS\Provider;

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
     * @throws \RescueMe\Finite\FiniteException
     * @throws Exception
     */
    function insert_trace_progress($mobile, $collapsed = false, $output=true) {

        $hours = Properties::get(Properties::TRACE_TIMEOUT, $mobile->user_id);
        
        $timeout = (time() - strtotime($mobile->alerted)) > $hours*60*60*1000;

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

            $trace['delivered']['time'] = T_('Unknown');
            $trace['delivered']['timestamp'] = '';

            if($mobile->responded !== null && $mobile->sms_sent !== null) {
                $trace['delivered']['state'] = 'warning';
                $trace['delivered']['tooltip'] =
                    T_('SMS is delivered, but delivery report from SMS provider not received');
            } else {

                $factory = Manager::get(Provider::TYPE, $mobile->user_id);

                /** @var Provider $sms */
                $sms = $factory->newInstance();

                // Create trace state machine
                $factory = new Factory();
                $machine = $factory->build($sms);
                $state = $machine->init()->apply($mobile);

                switch($state->getName())
                {
                    case NotDelivered::NAME:
                        $trace['delivered']['state'] = 'fail';
                        $trace['delivered']['tooltip'] =
                            sprintf(T_('SMS not delivered, provider reported %1$d'),$state->getData());
                        break;
                    case Timeout::NAME:
                        $trace['delivered']['state'] = 'fail';
                        $trace['delivered']['tooltip'] =
                            sprintf(T_('SMS not delivered after %1$d hours'),$hours) . '. '
                            . T_('The phone may be out of power or coverage.');
                        break;
                    case Sent::NAME:
                        $trace['delivered']['state'] = 'warning';
                        $trace['delivered']['tooltip'] =
                            sprintf(T_('SMS not delivered yet'),$hours) . '. '
                            . T_('The phone may be out of power or coverage.');
                        break;
                    default:
                        // Continue to check for other states.
                        // TODO: Rewrite to using Finite state on all cases
                }
            }
        }


        if($mobile->responded !== null) {
            // Get number of errors reported from mobile
            if($mobile->last_pos->timestamp>-1 || ($errors = $mobile->getErrors(true)) === FALSE) {
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


    /**
     * Insert trace progress bar
     * @param Mobile $mobile
     * @param bool $output
     * @return string
     * @throws Exception
     */
    function insert_last_position_table($mobile, $output=true) {

        $formats = array(
            array(Properties::MAP_DEFAULT_FORMAT => Properties::MAP_DEFAULT_FORMAT_UTM),
            array(Properties::MAP_DEFAULT_FORMAT => Properties::MAP_DEFAULT_FORMAT_DMM),
            array(Properties::MAP_DEFAULT_FORMAT => Properties::MAP_DEFAULT_FORMAT_DD),
            array(Properties::MAP_DEFAULT_FORMAT => Properties::MAP_DEFAULT_FORMAT_DMS),
        );

        $position = $mobile->last_pos;

        ob_start();
        require(ADMIN_PATH . "gui/trace.position.table.gui.php");
        $html = ob_get_clean();

        if($output) {
            echo $html;
        }
        return $html;
    }
    
