<?php
/**
 * File containing: Trace state `not delivered` class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 08. March 2019
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Mobile;


/**
 * Trace state `not delivered`
 * @package RescueMe\Finite\Trace\State
 */
class NotDelivered extends AbstractState {

    const NAME = 'Not delivered';

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
     */
    function accept($condition) {

        if($this->accepted = is_null($condition->sms_delivered) === true) {
            $messages = $condition->getUndeliveredMessages();
            $message = end($messages);
            if($this->accepted = $this->is_sms_error($message['message_provider_error']) === true){
                $this->data = $message->message_provider_error;
            }
        }

        return $this->accepted;
    }

    private function is_sms_error($status) {
        // Match all legacy values
        return !(is_null($status) || $status ==  '' || $status == 'Delivered (0)' || $status == 'Received by recipient');
    }
}