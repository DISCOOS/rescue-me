<?php

    /**
     * File containing: Asynchronous Number Lookup interface
     * 
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO Open Source Foundation}
     *
     * @since 5. April 2015
     * 
     * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
     */
    
    namespace RescueMe\SMS;

    /**
     * Lookup class
     * 
     * @package 
     */
    interface Lookup
    {
        /**
         * Send number lookup request
         * 
         * @param string $number Device phone number
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function request($number);

    }// Lookup
