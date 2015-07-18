<?php
/**
 * File containing: Trace state 'Rejected' class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 1. April 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Domain\Missing;


/**
 * Trace state Rejected
 * @package RescueMe\Finite\Trace
 */
class Rejected extends AbstractState {

    const NAME = 'Rejected';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_FINAL);
    }

    /**
     * Check if trace state is Rejected
     * @param \RescueMe\Domain\Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {
        // TODO: Add missing_aborted field to missing table
        $this->data = $condition->aborted;
        return is_null($this->data) === false;
    }

    /**
     * Get number of seconds since trace was Rejected
     * @return integer
     */
    public function getTimeSince() {
        return (int)(time() - strtotime($this->data));
    }



}