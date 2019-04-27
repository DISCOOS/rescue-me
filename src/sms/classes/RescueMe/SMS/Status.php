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

    use DateTime;
    use RescueMe\DBException;

    /**
     * Provider class
     * 
     * @package 
     */
    interface Status
    {
        /**
         * Update SMS delivery status.
         *
         * @param string $reference SMS Provider message reference
         * @param string $to Recipient phone number
         * @param bool $status Delivery status
         * @param DateTime $datetime Time of delivery
         * @param string $client_ref (optional) Client reference (only used if provider supports it)
         * @param string $error (optional) Error description
         * @param string $plnm (optional) Standard MCC/MNC tuple
         *
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function delivered($reference, $to, $status, $datetime, $client_ref='', $error='', $plnm='');
        
    }// Provider
