<?php

    /**
     * File containing: AbstractLookup lookup implementation
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Device;

    use RescueMe\AbstractModule;
    use RescueMe\Configuration;

    /**
     * Lookup base implementation
     *
     * @package RescueMe\Device
     */
    abstract class AbstractLookup extends AbstractModule implements Lookup {

        /**
         * Constructor
         *
         * @param $config Configuration Configuration
         * @param mixed $uses Uses (optional, default - empty array)
         *
         * @since 29. September 2013
         *
         */
        protected function __construct($config, $uses = array()) {

            // Forward to super class
            parent::__construct($config, $uses);

        }

        /**
         * Create minimal request
         * @param $request
         * @return array
         */
        public static function createRequest($request = null)
        {
            if(is_null($request)) {
                $request = $_SERVER;
            }

            if(function_exists('getallheaders')) {
                $request = array_merge(\getallheaders(), $request);
            }

            $keys = array (
                // User agents
                'UA',
                'HTTP_USER_AGENT',
                'HTTP_DEVICE_STOCK_UA',
                'HTTP_X_OPERAMINI_PHONE_UA',
                // User agent profiles
                'HTTP_X_WAP_PROFILE',
                'HTTP_PROFILE',
                'Opt',
                // Allows for detection of xml requests
                'Accept',
                // Miscellaneous
                'HTTP_ACCEPT_LANGUAGE'
            );

            return array_intersect_key($request, array_fill_keys($keys, null));

        }

    }