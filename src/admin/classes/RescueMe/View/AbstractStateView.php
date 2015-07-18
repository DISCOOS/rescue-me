<?php
/**
 * File containing: Abstract state view class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. April 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\View;

use RescueMe\Finite\Machine;
use RescueMe\Finite\State;


/**
 * Class AbstractStateView
 * @package RescueMe\View
 */
abstract class AbstractStateView extends AbstractView {

    /**
     * @var Machine
     */
    protected $machine;

    /**
     * @var mixed
     */
    protected $condition;

    /**
     * @var State
     */
    protected $state;

    /**
     * Default constructor
     *
     * @param $twig
     * @param $name
     * @param $machine
     */
    function __construct($twig, $name, $machine)
    {
        parent::__construct($twig, $name);
        $this->machine = $machine;
    }


    /**
     * Apply condition to view
     * @param $condition
     * @return boolean
     */
    public function apply($condition) {
        $this->condition = $condition;
        return $this->state = $this->machine->apply($condition);
    }


    /**
     * Render view from current state
     * @param array $context
     * @return string|boolean
     */
    public function render(array $context = array()) {
        // Determine current state
        if($this->state) {

            $context = $this->getContext(
                $this->condition,
                $this->state,
                $context);

            return parent::render($context);

        }

        return false;
    }


    /**
     * Get view context from given state
     * @param mixed $condition
     * @param State $state
     * @param array $context
     * @return array
     */
    abstract protected function getContext($condition, $state, array $context);

} 