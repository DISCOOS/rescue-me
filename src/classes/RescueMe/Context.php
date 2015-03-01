<?php
    /**
     * File containing: Application context
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;

    /**
     * Application Context
     * @package RescueMe
     */
    class Context {

        /**
         * Constant
         */
        const APP_PATH = 'app_path';

        /**
         * Constant
         */
        const DATA_PATH = 'data_path';

        /**
         * Constant
         */
        const VENDOR_PATH = 'vendor_path';

        /**
         * Constant
         */
        const LOCALE_PATH = 'locale_path';

        /**
         * Internal cache
         * @var boolean|array
         */
        private static $context = FALSE;

        /**
         * Load context
         * @param mixed $context
         */
        public static function load($context) {
            Context::$context = $context;
        }


        /**
         * Get application root path
         * @return string
         */
        public static function getAppPath() {
            return isset_get(Context::$context, Context::APP_PATH);
        }

        /**
         * Get application data path
         * @return string
         */
        public static function getDataPath() {
            return isset_get(Context::$context, Context::DATA_PATH);
        }

        /**
         * Get vendor path
         * @return string
         */
        public static function getVendorPath() {
            return isset_get(Context::$context, Context::VENDOR_PATH);
        }

        /**
         * Get locale path
         * @return string
         */
        public static function getLocalePath() {
            return isset_get(Context::$context, Context::LOCALE_PATH);
        }

    }