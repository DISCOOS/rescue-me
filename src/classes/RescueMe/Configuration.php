<?php

    /**
     * File containing: Configuration class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO Open Source Foundation} 
     *
     * @since 16. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;

    /**
     * Configuration class
     * 
     * @package 
     */
    class Configuration
    {
        private $params;
        private $labels;
        private $required;
        
        /**
         * Constructor
         *
         * @param array $params
         * @param array $labels
         * @param array $required
         * 
         */
        public function __construct($params, $labels = array(), $required = array())
        {
            $this->params = $params;
            $this->labels = $labels;
            $this->required = $required;
        }// __construct
        
        
        /**
         * Get parameter
         * @param string $parameter Parameter name
         * @param mixed|null $defaultValue Default value (optional, default - null);
         * @return mixed|null.
         */
        public function get($parameter, $defaultValue = null) {
            return isset($this->params[$parameter]) ? $this->params[$parameter] : $defaultValue;
        }
        
        
        /**
         * Set parameter
         * @param string $parameter
         * @param mixed $value
         * @return boolean TRUE if set, FALSE otherwise.
         */
        public function set($parameter, $value) {
            if(isset($this->params[$parameter])) {
                $this->params[$parameter] = $value;
            }
            return isset($this->params[$parameter]);
        }
        
        
        /**
         * Get keys
         * @return array
         */
        public function keys() {
            return array_keys($this->params);
        }
        
        
        /**
         * Get params
         * @return array
         */
        public function params() {
            return $this->params;
        }
        
        
        /**
         * Get parameter label
         * @param string $parameter
         * @return string
         */
        public function label($parameter) {
            return isset($this->labels[$parameter]) ? $this->labels[$parameter] : null;
        }
        
        
        /**
         * Check if parameter is required
         * @param string $parameter
         * @return mixed
         */
        public function required($parameter) {
            return in_array($parameter, $this->required);
        }
        
        
    }// Configuration
