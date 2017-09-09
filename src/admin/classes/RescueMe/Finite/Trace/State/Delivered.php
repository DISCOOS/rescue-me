<?php
/**
 * File containing: Trace state delivered class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 08. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Locale;
use RescueMe\Mobile;
use RescueMe\SMS\Check;
use RescueMe\SMS\Provider;


/**
 * Trace state delivered
 * @package RescueMe\Finite\Trace\Trace\State
 */
class Delivered extends AbstractState {

    const NAME = 'Delivered';

    /**
     * @var \RescueMe\SMS\Provider
     */
    private $sms;

    /**
     * Constructor
     * @param Provider $sms
     */
    function __construct($sms) {
        parent::__construct(self::NAME, State::T_TRANSIT);

        if($sms instanceof Check) {
            $this->sms = $sms;
        }
    }

    /**
     * Check trace request is delivered
     * @param Mobile $condition
     * @return boolean
     */
    function accept($condition) {

        // Check SMS status?
        if(is_null($this->sms) === false) {
            $code = Locale::getDialCode($condition->country);
            $code = $this->sms->accept($code);
            $ref = $condition->sms_provider_ref;
            // Check request status?
            if(!empty($ref) && $this->sms->request($ref,$code.$condition->number)) {
                $condition = Mobile::get($condition->id);
            }
        }

        $this->data = $condition->sms_delivered;
        return $this->accepted = is_null($this->data) === false;
    }
}