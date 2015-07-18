<?php
/**
 * File containing: Trace state DeliveryTimeout class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 27. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite\Trace\State;

use RescueMe\DB;
use RescueMe\Finite\AbstractState;
use RescueMe\Finite\State;
use RescueMe\Domain\Missing;


/**
 * Class DeliveryTimeout
 * @package RescueMe\Finite\Trace\Trace\State
 */
class DeliveryTimeout extends AbstractState {

    const NAME = 'DeliveryTimeout';

    /**
     * Timeout after 16 minutes
     */
    const TIMEOUT = 960;

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
     * Check if trace state is DeliveryTimeout
     * @param \RescueMe\Domain\Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {

        if(is_null($condition->message_delivered) && is_null($condition->message_sent) === false) {
            $this->data = $condition->message_sent;
        } else {
            $this->data = 0;
        }
        return $this->getTimeSince() > self::TIMEOUT;
    }

    /**
     * Get number of seconds since SMS was sent
     * @return integer
     */
    public function getTimeSince() {
        return (int)(time() - strtotime($this->data));
    }


}