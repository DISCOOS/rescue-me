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

    use Closure;
    use RescueMe\Module;

    /**
     * Provider class
     * 
     * @package 
     */
    interface Provider extends Module
    {
        /**
         * SMS provider module type
         */
        const TYPE = 'RescueMe\SMS\Provider';


        /**
         * Send SMS message to given number.
         *
         * @param string $code ISO country code
         * @param string $number Recipient phone number without dial code
         * @param string $text SMS message text
         * @param string $client_ref (optional) Client reference (only used if provider supports it)
         * @param $on_error (optional) Closure that returns string logged with error message
         *
         * @return bool|array Provider message references, FALSE on failure
         */
        public function send($code, $number, $text, $client_ref = null, $on_error = null);

        
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
        
    }// Provider
