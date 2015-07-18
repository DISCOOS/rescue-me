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
use RescueMe\Domain\Missing;
use RescueMe\Domain\Position;
use RescueMe\Properties;


/**
 * Trace state located
 * @package RescueMe\Finite\Trace\State
 */
class Located extends AbstractState {

    const NAME = 'Located';

    /**
     * Parameter name
     */
    const LOCATION_DESIRED_ACC = Properties::LOCATION_DESIRED_ACC;

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
     * Check if trace state is Located
     * @param Missing $condition
     * @return mixed
     */
    protected function onAccept($condition) {
        $this->data = $condition;
        return $this->getMostAccurate() !== false;
    }

    /**
     * Check if position is of desired accuracy or better
     */
    public function isAccurate() {

        return $this->isAccepted() &&
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