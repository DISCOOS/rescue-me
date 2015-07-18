<?php
/**
 * File containing: Trace state LocationTimeout class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 27. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Domain\Missing;
use RescueMe\Properties;


/**
 * Trace state LocationTimeout
 * @package RescueMe\Finite\Trace\Trace\State
 */
class LocationTimeout extends AbstractState {

    const NAME = 'LocationTimeout';

    /**
     * Parameter name
     */
    const LOCATION_MAX_WAIT = Properties::LOCATION_MAX_WAIT;

    /**
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
     * Check if trace state is LocationTimeout
     * @param \RescueMe\Domain\Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {

        if(is_null($condition->last_pos)
            && is_null($condition->message_delivered)
            && is_null($condition->message_sent) === false) {
            $this->data = (int)(time() - strtotime($condition->message_sent));
        } else {
            $this->data = 0;
        }
        return $this->data > $this->getMaxWaitTime();
    }

    /**
     * Get maximum wait time for loction
     * @return integer
     */
    public function getMaxWaitTime() {
        return (int)$this->params[self::LOCATION_MAX_WAIT];
    }

}