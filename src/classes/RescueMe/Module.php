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
     * RescueMe module interface
     *
     * @package RescueMe
     *
     * @property Integer $id Module id
     * @property String $type Module type class
     * @property String $impl Module implementation class
     * @property Mixed $config Module configuration
     */
    interface Module {

        /**
         * Fatal error constant
         */
        const FATAL = -1;


        /**
         * Check if module is supported by host system
         * @return boolean
         */
        public function isSupported();


        /**
         * Get module configuration
         * @return \RescueMe\Configuration
         */
        public function getConfig();


        /**
         * Validate configuration
         *
         * Returns TRUE if configuration is valid, FALSE otherwise.
         *
         * Check Module::errno() and Module::error() for more information about if validate does not succeed.
         *
         * @param \RescueMe\Configuration $config Account [optional, null - verify current]
         *
         * @return boolean|string
         */
        public function validate($config = null);


        /**
         * Initialize module
         *
         * Returns TRUE if initialization succeeded, FALSE otherwise.
         *
         * Check Module::errno() and Module::error() for more information if initialization does not succeed.
         *
         * @param boolean $update Allow update if already initialized
         * @return boolean
         */
        public function init($update = false);


        /**
         * Returns the error code for the most recent function call.
         *
         * @return integer An error code value for the last call, if it failed. zero means no error occurred.
         */
        public function errno();


        /**
         * Returns a string description of the last error.
         *
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public function error();


    }