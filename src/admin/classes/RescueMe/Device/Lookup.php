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

    use RescueMe\Module;
    use RescueMe\Domain\Device;

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
         * Device name
         */
        const DEVICE_TYPE = 'device_type';

        /**
         * Device operating system name
         */
        const DEVICE_OS_NAME = 'device_os_name';

        /**
         * Device operating system version
         */
        const DEVICE_OS_VERSION = 'device_os_version';

        /**
         * Device browser name
         */
        const DEVICE_BROWSER_NAME = 'device_browser_name';

        /**
         * Device browser version
         */
        const DEVICE_BROWSER_VERSION = 'device_browser_version';

        /**
         * Is device phone flag
         */
        const DEVICE_IS_PHONE = 'device_is_phone';

        /**
         * Is device smartphone flag
         */
        const DEVICE_IS_SMARTPHONE = 'device_is_smartphone';

        /**
         * Is W3C XHR2 supported flag
         */
        const DEVICE_SUPPORTS_XHR2 = 'device_supports_xhr2';

        /**
         * Is W3C geolocation api supported flag
         */
        const DEVICE_SUPPORTS_GEOLOCATION = 'device_supports_geolocation';
        
        /**
         * Get device configuration from given request
         *
         * @param $request Mixed Device request
         * 
         * @return bool|Device
         */
        public function device($request);


        /**
         * Returns last error code and message for the most recent function call.
         *
         * @return bool|array Array with error code and message if the last call failed, FALSE otherwise.
         */
        public function last_error();

        /**
         * Returns the error code for the most recent function call.
         *
         * @return integer An error code value for the last call, if it failed. zero means no error occurred.
         */
        public function last_error_code();


        /**
         * Returns a string description of the last error.
         *
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public function last_error_message();


    }// Lookup
