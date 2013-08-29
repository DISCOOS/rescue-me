<?php

    /**
     * File containing: Check interface
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO Open Source Foundation} 
     *
     * @since 29. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
     */
    
    namespace RescueMe\SMS;

    /**
     * Callback class
     * 
     * @package 
     */
    interface Check extends Status
    {
        /**
         * Request SMS status from provider
         * 
         * @param string $provider_ref Message id
         * @param string $to Recipient phone number
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function request($provider_ref,$number);
        
        
    }// Check
