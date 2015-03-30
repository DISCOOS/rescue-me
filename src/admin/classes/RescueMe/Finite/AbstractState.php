<?php
/**
 * File containing: Abstract state class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 08. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite;


/**
 * Class AbstractState
 * @package RescueMe\Finite
 */
abstract class AbstractState implements State {

    /**
     * State name
     * @var string
     */
    private $name;

    /**
     * State type
     * @var string
     */
    private $type;

    /**
     * State data
     * @var mixed
     */
    protected $data;

    /**
     * State accepted flag
     * @var boolean
     */
    private $accepted;

    /**
     * Constructor
     * @param $name string Unique state name
     * @param $type string State type
     * @param $data mixed State data
     */
    function __construct($name, $type, $data = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->data = $data;
    }


    /**
     * Get state name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get state type
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get state data
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check if state has been accepted
     * @return boolean
     */
    function isAccepted()
    {
        return $this->accepted;
    }

    /**
     * Check if state accepts condition
     * @param mixed $condition
     * @return boolean
     */
    final function accept($condition) {
        return ($this->accepted = $this->onAccept($condition));
    }

    /**
     * State condition check implementation
     *
     * @param $condition
     * @return boolean
     */
    abstract protected function onAccept($condition);




}