<?php

    /**
     * File containing: Set SMS status interface
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 12. July 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */
    
    namespace RescueMe\SMS;
    use RescueMe\Domain\Missing;

    /**
     * SetStatus interface
     * 
     * @package 
     */
    interface SetStatus
    {       
        /**
         * Register SMS delivered to given number
         * 
         * @param string $reference Reference to SMS sent by provider
         * @param string $to Recipient phone number without country code
         * @param string $status Delivery status
         * @param \DateTime $datetime Time of delivery
         * @param string $error Error description
         *
         * @return boolean TRUE if status was updated, FALSE otherwise
         * 
         */
        public function delivered($reference, $to, $datetime, $status, $error);
        
    }
