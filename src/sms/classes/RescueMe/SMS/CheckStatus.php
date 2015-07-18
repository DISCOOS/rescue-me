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
     * Check interface
     * 
     * @package 
     */
    interface CheckStatus
    {
        /**
         * Check SMS status request to provider
         * 
         * @param string $reference Provider SMS reference
         * @param string $number SMS recipient phone number
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function check($reference, $number);
        
        
    }
