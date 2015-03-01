<?php
    /**
     * File containing: Manager class
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 01. March 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;


    /**
     * Module factory class
     *
     * @package RescueMe
     */
    class Factory {

        public $id;

        public $type;

        public $impl;

        public $config;

        public $user_id;

        /**
         * Constructor
         * @param array $module Module definition
         */
        public function __construct($module)
        {
            $this->id = $module['module_id'];
            $this->type = ltrim($module['type'],"\\");
            $this->impl = ltrim($module['impl'],"\\");
            $this->config = json_decode(isset_get($module, 'config', array()), true);
            $this->user_id = isset_get($module,'user_id', 0);
        }

        /**
         * Get new configuration
         *
         * @return Configuration
         *
         */
        public function newConfig() {
            /** @var Module $instance */
            $instance = new $this->impl;
            return $instance->getConfig();
        }


        /**
         * Create module instance.
         *
         * Returns module instance  of FALSE on error.
         *
         * @param boolean $empty Create empty instance if true
         *
         * @return Module|false
         */
        public function newInstance($empty=false) {

            $reflect  = new \ReflectionClass($this->impl);

            $invoke = array($reflect,'newInstance');

            $config[0] = $this->user_id;

            $config = array_merge($config, ($empty ? $this->newConfig()->params() : $this->config));

            return call_user_func_array($invoke, $config);
        }


    }