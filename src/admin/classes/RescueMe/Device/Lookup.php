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
         * Yes flag
         */
        const YES = 1;

        /**
         * Yes flag
         */
        const NO = 0;

        /**
         * Unknown flag
         */
        const UNKNOWN = -1;

        /**
         * Device lookup module type
         */
        const TYPE = 'RescueMe\Device\Lookup';

        /**
         * Handset name
         */
        const HANDSET_NAME = 'handset_name';

        /**
         * Handset operating system
         */
        const HANDSET_OS = 'handset_os';

        /**
         * Handset browser
         */
        const HANDSET_BROWSER = 'handset_browser';

        /**
         * Handset model name
         */
        const MODEL_NAME = 'model_name';

        /**
         * Is generic device flag
         */
        const IS_GENERIC = 'is_generic';

        /**
         * Is smartphone flag
         */
        const IS_SMARTPHONE = 'is_smartphone';

        /**
         * Is W3C geolocation api supported flag
         */
        const SUPPORTS_GEOLOC = 'supports_geoloc';


        /**
         * Create request
         * @return array
         */
        public static function createRequest();

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
