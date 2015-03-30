<?php
/**
 * File containing: Trace state located class
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
 * Trace state located
 * @package RescueMe\Finite\Trace\State
 */
class Located extends AbstractState {

    const NAME = 'Located';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(self::NAME, State::T_FINAL);
    }

    /**
     * Check if state accepts condition
     * @param Missing $condition
     * @return mixed
     */
    protected function onAccept($condition) {
        $this->data = $condition->last_pos;
        return (is_null($this->data) === false && $this->data->pos_id !== -1);
    }

}