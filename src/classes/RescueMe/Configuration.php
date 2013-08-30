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
        public function __construct($params,$labels,$required)
        {
            $this->params = $params;
            $this->labels = $labels;
            $this->required = $required;
        }// __construct
        
        
        /**
         * Get property
         * @param string $property
         * @return mixed|null.
         */
        public function get($property) {
            return isset($this->params[$property]) ? $this->params[$property] : null;
        }
        
        
        /**
         * Set property
         * @param string $property
         * @param mixed $value
         * @return boolean TRUE if set, FALSE otherwise.
         */
        public function set($property, $value) {
            if(isset($this->params[$property])) {
                $this->params[$property] = $value;          
            }
            return isset($this->params[$property]);
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
         * Get property label
         * @param string $property
         * @return string
         */
        public function label($property) {
            return isset($this->labels[$property]) ? $this->labels[$property] : null;
        }
        
        
        /**
         * Check if property is required
         * @param string $property
         */
        public function required($property) {
            return in_array($property, $this->required);
        }
        
        
    }// Configuration
