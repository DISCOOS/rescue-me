<?php

    /**
     * File containing: Status interface
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 12. July 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */
    
    namespace RescueMe\SMS;

    /**
     * Provider class
     * 
     * @package 
     */
    interface Status
    {       
        /**
         * Register that a message has been delivered.
         * 
         * @param string $provider_ref Message id
         * @param string $to Recipient phone number
         * @param bool $status Deliverystatus
         * @param \DateTime $datetime Time of delivery
         * @param string $errorDesc Error description
         * 
         */
        public function delivered($provider_ref,$to,$datetime,$status,$errorDesc);
        
    }// Provider
