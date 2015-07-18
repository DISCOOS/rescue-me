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
use RescueMe\Domain\Missing;
use RescueMe\SMS\CheckStatus;
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

        if($sms instanceof CheckStatus) {
            $this->sms = $sms;
        }
    }

    /**
     * Check trace state is Delivered
     * @param Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {

        // Check SMS status?
        if(is_null($this->sms) === false) {
            $code = Locale::getDialCode($condition->number_country_code);
            $code = $this->sms->accept($code);
            $ref = $condition->message_reference;
            // Check request status?
            if(!empty($ref) && $this->sms->check($ref,$code.$condition->number)) {
                $condition = Missing::get($condition->id);
            }
        }

        $this->data = $condition->message_delivered;
        return is_null($this->data) === false;
    }

    /**
     * Get number of seconds since SMS was sent
     * @return integer
     */
    public function getTimeSince() {
        return (int)(time() - strtotime($this->data));
    }


}