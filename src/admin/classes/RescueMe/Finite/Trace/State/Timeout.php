<?php
/**
 * File containing: Trace state `timeout` class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 08. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use DateTime;
use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Mobile;
use RescueMe\Properties;


/**
 * Trace state `timeout`
 * @package RescueMe\Finite\Trace\State
 */
class Timeout extends AbstractState {

    const NAME = 'Timeout';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_FINAL);
    }

    /**
     * Check if trace exists
     * @param Mobile $condition
     * @return mixed
     * @throws \Exception
     */
    function accept($condition) {

        if($this->accepted = is_null($condition->sms_delivered) === true) {
            $timeout = Properties::get(Properties::TRACE_TIMEOUT, $condition->user_id);

            $date = new DateTime();
            $date->setTimestamp(strtotime($condition->sms_sent));
            $delta = $date->diff(new DateTime())->h;

            if($this->accepted = $delta > $timeout){
                $this->data = $delta;
            }
        }

        return $this->accepted;
    }
}