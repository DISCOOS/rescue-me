<?php
    /**
     * File containing: Finite state machine class
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 08. March 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Finite;


    /**
     * Class Machine
     * @package RescueMe\Finite
     */
    class Machine {

        /**
         * Machine states
         * @var array
         */
        private $states = array();

        /**
         * Next possible states from current
         * @var array
         */
        private $next = array();

        /**
         * State transitions
         * @var array
         */
        private $transitions = array();

        /**
         * Current state
         * @var State
         */
        private $current = null;

        /**
         * Machine constructor
         *
         * @param $states array Initial states
         * @param $transitions array Initial transitions
         */
        function __construct($states = array(), $transitions = array())
        {
            $this->states = $states;
            $this->transitions = $transitions;
        }

        /**
         * Initialize machine state
         * @throws FiniteException
         * @return Machine
         */
        public function init() {

            $states = array();

            // Analyze states
            foreach($this->states as $state) {
                /** @var $state State */
                $name = $state->getName();
                if(in_array($name, array_keys($states))) {
                    throw new FiniteException("Found duplicate state name: $name",
                        FiniteException::ILLEGAL_STATE);
                }
                $states[$name] = $state;
            }

            $final = 0;
            $next = array();

            // Analyze transitions
            foreach($this->transitions as $transition) {
                /** @var $name string */
                /** @var $transition Transition */
                $name = $transition->getInbound();
                /** @var $state State */
                $state = Machine::assertState($states, $name);
                $type = $state->getType();
                if($type === State::T_INITIAL) {
                    $next[$name] = $state;
                }
                $state = Machine::assertState($states, $transition->getOutbound());
                if($state->getType() === State::T_FINAL) {
                    $final++;
                }
            }

            $initial = count($next);

            // Sanity checks
            if($initial === 0)
                throw new FiniteException("No initial states found",
                    FiniteException::ILLEGAL_STATE);
            if($initial > 1)
                throw new FiniteException("Only one initial state allowed, found $initial",
                    FiniteException::ILLEGAL_STATE);
            if($final === 0)
                throw new FiniteException("No final states found",
                    FiniteException::ILLEGAL_STATE);


            // Initialize
            $this->next = $next;
            $this->states = $states;
            $this->current = reset($this->next);

            return $this;
        }

        /**
         * Assert state
         * @param $states
         * @param $name
         * @throws FiniteException
         * @return State
         */
        private static function assertState($states, $name) {
            /** @var $state State */
            $state = $states[$name];

            if(is_null($state)) {
                throw new FiniteException("State not found: $name",
                    FiniteException::NOT_FOUND);
            } else if($state instanceof State === false){
                throw new FiniteException("Interface not implemented: $name" ,
                    FiniteException::ILLEGAL_TYPE);
            }
            return $state;
        }


        /**
         * Get current state
         * @return State
         */
        public function getCurrent()
        {
            return $this->current;
        }

        /**
         * Add new machine state
         * @param State $state
         * @return Machine
         */
        public function addState(State $state) {
            $this->states[] = $state;
            return $this;
        }

        /**
         * Get all state names
         * @return array
         */
        public function getStates()
        {
            return $this->states;
        }

        /**
         * Add new transition to machine
         * @param string $in Inbound state
         * @param string $out Outbound state
         * @param $action Action Transition action (optional)
         * @return Machine
         */
        public function addTransition($in, $out, $action = null) {
            $this->transitions[] = new Transition($in, $out, $action);
            return $this;
        }

        /**
         * Get all transitions
         * @return array
         */
        public function getTransitions()
        {
            return $this->transitions;
        }

        /**
         * Apply condition to state machine
         * @param mixed $condition
         * @return boolean|State
         */
        public function apply($condition) {

            $state = $this->getCurrent();

            if($state->getType() === State::T_FINAL) {
                return $state->accept($condition);
            }

            if($state = $this->accept($condition, $this->next)) {
                $this->current = $state;
            }

            return $state;

        }

        /**
         * Check if condition is accepted by any given state
         * @param mixed $condition
         * @param array $next
         * @return boolean|State
         */
        private function accept($condition, $next) {

            /** @var $state State */
            foreach($next as $state) {
                if($state->accept($condition)) {
                    $this->next = $this->getNext($state);
                    if(empty($this->next) === false) {
                        if($next = $this->accept($condition, $this->next)) {
                            $state = $next;
                        }
                    }
                    return $state;
                }
            }

            return false;
        }

        /**
         * Get next states from given
         * @param State $state
         * @return array
         */
        private function getNext($state) {

            if($state->getType() === State::T_FINAL) {
                return array();
            }

            $next = array();

            // Get next possible states from current state
            foreach($this->transitions as $transition) {
                /** @var $transition Transition */
                if($transition->getInbound() === $state->getName()) {
                    $name = $transition->getOutbound();
                    if($this->states[$name] instanceof State) {
                        $next[$name] = $this->states[$name];
                    }
                }
            }

            return $next;
        }

    }