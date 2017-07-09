<?php
/**
 * File containing: Trace state sent class
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
use RescueMe\Missing;


/**
 * Trace state not sent
 * @package RescueMe\Finite\Trace\State
 */
class NotSent extends AbstractState {

    const NAME = 'Not sent';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_FINAL);
    }

    /**
     * Check if trace exists
     * @param Missing $condition
     * @return mixed
     */
    function accept($condition) {
        $this->data = $condition->reported;
        return $this->accepted = is_null($condition->sms_sent) === true;
    }
}