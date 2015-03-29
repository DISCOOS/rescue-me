<?php
/**
 * File containing: Trace state alerted class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Missing;


/**
 * Trace state alerted
 * @package RescueMe\Finite\Trace\State
 */
class Alerted extends AbstractState {

    const NAME = 'Alerted';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_INITIAL);
    }

    /**
     * Check if trace exists
     * @param Missing $condition
     * @return mixed
     */
    function accept($condition) {
        $this->data = $condition->sms_sent;
        return $this->accepted = is_null($this->data) === false;
    }
}