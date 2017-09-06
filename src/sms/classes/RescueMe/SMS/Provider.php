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

    use RescueMe\Module;
    use RescueMe\User;

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
         * @param int|User $user User
         * @param string $code International dial code
         * @param string $to Recipient phone number without dial code
         * @param string $message Message text
         * 
         * @return mixed|array Message id if success, FALSE otherwise.
         */
        public function send($user, $code, $to, $message);

        
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
