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
         * Sent state
         */
        const SENT = 'sent';

        /**
         * Delivered state
         */
        const DELIVERED = 'delivered';

        /**
         * Error state
         */
        const ERROR = 'error';

        /**
         * Send SMS message to given number.
         * 
         * @param string $from Sender
         * @param string $code International dial code
         * @param string $to Recipient phone number without dial code
         * @param string $text Message text
         * @param integer $userId User id
         *
         * @return integer|array Message id if success, FALSE otherwise.
         */
        public function send($from, $code, $to, $text, $userId);

        
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
