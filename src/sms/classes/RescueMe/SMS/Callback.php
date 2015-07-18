<?php

    /**
     * File containing: Callback interface
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
    interface Callback extends SetStatus
    {
        const URL = "sms/callback/";

        const PROPERTY = "callback";

        /**
         * Handle given status
         * 
         * @param mixed $params
         * 
         * @return boolean
         */
        public function handle($params);
        
        
    }// Callback
