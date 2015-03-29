<?php
/**
 * File containing: Transition class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 08. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite;


/**
 * Class Transition
 * @package RescueMe\Finite
 */
class Transition {

    /**
     * Inbound state name
     * @var string
     */
    private $in;

    /**
     * Outbound state name
     * @var string
     */
    private $out;

    /**
     * Transition action
     * @var Action
     */
    protected $action;

    /**
     * Constructor
     * @param $in string Inbound state name
     * @param $out string Outbound state name
     * @param $action Action Transition action
     */
    function __construct($in, $out, $action = null)
    {
        $this->in = $in;
        $this->out = $out;
        $this->action = $action;
    }


    /**
     * Get inbound state name
     * @return string
     */
    public function getInbound()
    {
        return $this->in;
    }

    /**
     * Get outbound state name
     * @return string
     */
    public function getOutbound()
    {
        return $this->out;
    }

    /**
     * Get action
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

}