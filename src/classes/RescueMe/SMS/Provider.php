<?php

    /**
     * File containing: Provider interface
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;

    /**
     * Provider class
     * 
     * @package 
     */
    interface Provider
    {
        /**
         * Fatal error constant
         */
        const FATAL = -1;
        
        /**
         * Get configuration
         * 
         * @return array Associative array of parameters.
         */
        public function config();
        
        /**
         * Send SMS message to given number.
         * 
         * @param string $from Sender
         * @param string $code International dial code
         * @param string $to Recipient phone number without dial code
         * @param string $message Message text
         * 
         * @return mixed|array Message id if success, FALSE otherwise.
         */
        public function send($from, $code, $to, $message);
        
        /**
         * Get supported country dial code pattern.
         * 
         * See {@link http://countrycode.org/ Country Codes} for more information.
         * 
         * @return string Country code as 
         * {@link http://www.php.net/manual/en/pcre.pattern.php PCRE pattern}
         */
        public function getDialCodePattern();
        
        
        /**
         * Check if provider accepts given telephone number.
         * 
         * @param mixed $number
         * 
         * @return boolean|string. Accepted code, FALSE otherwise
         */
        public function accept($number);
        
        
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
        
        
    }// Provider
