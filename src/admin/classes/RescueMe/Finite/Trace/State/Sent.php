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
use RescueMe\Domain\Missing;


/**
 * Trace state sent
 * @package RescueMe\Finite\Trace\State
 */
class Sent extends AbstractState {

    const NAME = 'Sent';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_TRANSIT);
    }

    /**
     * Check if trace state is Sent
     * @param Missing $condition
     * @return mixed
     */
    protected function onAccept($condition) {
        $this->data = $condition->message_sent;
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