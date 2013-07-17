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
         * Get configuration
         * 
         * @return array Associative array of parameters.
         */
        public function config();
        
        /**
         * Send SMS message to given number.
         * 
         * @param string $to Recipient phone number
         * @param string $from Sender phone number
         * @param string $message Message text
         * 
         * @return mixed Message id (mixed) if success, errors otherwise (array).
         */
        public function send($to,$from,$message);
        
    }// Provider
