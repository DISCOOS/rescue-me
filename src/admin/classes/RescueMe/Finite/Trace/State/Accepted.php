<?php
/**
 * File containing: Trace state responded class
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
 * Trace state accepted
 * @package RescueMe\Finite\Trace
 */
class Accepted extends AbstractState {

    const NAME = 'Answered';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_TRANSIT);
    }

    /**
     * Check if request is accepted
     * @param Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {
        $this->data = $condition->answered;
        return is_null($this->data) === false;
    }

}