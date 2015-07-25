<?php
/**
 * File containing: Trace state 'Closed' class
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
use RescueMe\Domain\Operation;
use RescueMe\Domain\Position;
use RescueMe\Properties;


/**
 * Trace state Closed
 * @package RescueMe\Finite\Trace
 */
class Closed extends AbstractState {

    const NAME = 'Closed';

    /**
     * Parameter name
     */
    const LOCATION_DESIRED_ACC = Properties::LOCATION_DESIRED_ACC;

    /**
     * @var \RescueMe\Domain\Operation
     */
    private $operation;

    /**
     * Parameters
     * @var array
     */
    private $params;

    /**
     * Constructor
     * @param array $params Parameters
     */
    function __construct($params) {
        parent::__construct(self::NAME, State::T_TRANSIT);
        $this->params = $params;
    }

    /**
     * Check if trace state is Closed
     * @param \RescueMe\Domain\Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {
        $this->data = $condition;
        $this->operation = Operation::get($condition->op_id);
        return is_null($this->operation->op_closed) === false;
    }

    /**
     * Get operation
     * @return \RescueMe\Domain\Operation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Get number of seconds since operation was closed
     * @return integer
     */
    public function getTimeSince() {
        return (int)(time() - strtotime($this->data->op_closed));
    }

    /**
     * Check if location exists
     * @return boolean
     */
    public function isLocated() {
        return is_null($this->data->last_pos) !== null;
    }


    /**
     * Check if position is of desired accuracy or better
     */
    public function isAccurate() {

        return $this->isAccepted() && $this->isLocated() &&
        $this->getMostAccurate()->acc <= $this->getDesiredAccuracy();
    }


    /**
     * Get desired accuracy
     * @return integer
     */
    public function getDesiredAccuracy() {
        return (int)$this->params[self::LOCATION_DESIRED_ACC];
    }

    /**
     * Get most accurate position
     * @return \RescueMe\Domain\Position
     */
    public function getMostAccurate() {
        return $this->data->getMostAccurate();
    }

}