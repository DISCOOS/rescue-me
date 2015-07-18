<?php
/**
 * File containing: Trace state TraceTimeout class
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


/**
 * Class TraceTimeout
 * @package RescueMe\Finite\Trace\Trace\State
 */
class TraceTimeout extends AbstractState {

    const NAME = 'TraceTimeout';

    /**
     * Timeout after 1 day
     */
    const TIMEOUT = 86400;

    /**
     * @var array
     */
    private $params;

    /**
     * Constructor
     * @param array $params
     */
    function __construct($params) {
        parent::__construct(self::NAME, State::T_FINAL);
        $this->params = $params;
    }

    /**
     * Check if trace state is TraceTimeout
     * @param Missing $condition
     * @return boolean
     */
    protected function onAccept($condition) {

        if(is_null($condition->last_pos) && is_null($condition->reported) === false) {
            $this->data = (int)(time() - strtotime($condition->reported));
        } else {
            $this->data = 0;
        }
        return $this->data > self::TIMEOUT;
    }
}