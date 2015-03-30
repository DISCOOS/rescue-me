<?php

    /**
     * File containing: Lookup interface
     * 
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\Device;

    use RescueMe\Configuration;
    use RescueMe\Module;

    /**
     * Lookup class
     * 
     * @package 
     */
    interface Lookup extends Module
    {
        /**
         * Device lookup module type
         */
        const TYPE = 'RescueMe\Device\Lookup';

        /**
         * Create request
         * @return array
         */
        public function createRequest();

        /**
         * Get device configuration from given request
         *
         * @param $request array Device request
         * 
         * @return Configuration
         */
        public function device($request);

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


    }// Lookup
