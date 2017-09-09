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
use RescueMe\Mobile;


/**
 * Trace state accepted
 * @package RescueMe\Finite\Trace
 */
class Responded extends AbstractState {

    const NAME = 'Responded';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_TRANSIT);
    }

    /**
     * Check if request is accepted
     * @param Mobile $condition
     * @return boolean
     */
    function accept($condition) {
        $this->data = $condition->responded;
        return $this->accepted = is_null($this->data) === false;
    }

}