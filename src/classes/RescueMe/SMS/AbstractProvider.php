<?php

    /**
     * File containing: AbstractProvider class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 29. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;
    
    use \RescueMe\DB;
    use \RescueMe\Locale;

    /**
     * AbstractProvider class
     * 
     * @package 
     */
    abstract class AbstractProvider implements Provider, Status
    {
        /**
         * Provider configuration
         * 
         * @var \RescueMe\Configuration
         */
        protected $config;
        
        /**
         * Description of last error
         * @var array
         */
        protected $error;
        
        
        /**
         * Get provider configuration
         * @return \RescueMe\Configuration
         */
        public function config()
        {
            return clone($this->config);
        }
        
        
        /**
         * Set last error from exception.
         * @param \Exception $e Exception
         */
        protected function exception(\Exception $e) {
            $this->error['code'] = $e->getCode();
            $this->error['message'] = $e->getMessage();
        }
        
        
        /**
         * Set fatal error
         * @param string $message
         */
        protected function fatal($message) {
            $this->error['code'] = Provider::FATAL;
            $this->error['message'] = $message;
        }
        
        /**
         * Returns the error code for the most recent function call.
         * 
         * @return integer An error code value for the last call, if it failed. zero means no error occurred.
         */
        public function errno()
        {
            return isset($this->error) ? $this->error['code'] : 0;
        }
        

        /**
         * Returns a string description of the last error.
         * 
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public function error()
        {
            return isset($this->error) ? $this->error['message'] : '';
        }        
        
        
        /**
         * Send SMS message to given number.
         * 
         * @param string $from Sender
         * @param string $code International dial code
         * @param string $to Recipient phone number without dial code
         * @param string $message Message text
         * 
         * @return mixed|array Message id if success, FALSE otherwise.
         */
        public function send($from, $country, $to, $message)
        {
            // Prepare
            unset($this->error);
            
            if(!($code = \RescueMe\Locale::getDialCode($country)))
            {
                return $this->fatal("Failed to get country dial code [$country]");
            }               
                
            if(!($code = $this->accept($code))) {
                return $this->fatal("SMS provider does not accept country dial code [$code]");
            }

            return $this->_send($from, $code.$to, $message, $this->config()->params());
            
        }// send

        
        /**
         * Actual send implementation
         * 
         * @param string $from Sender
         * @param string $to Recipient international phone number
         * @param string $message Message text
         * @param array $account Provider configuration
         */
        protected abstract function _send($from, $to, $message, $account);

        
        /**
         * Update SMS delivery status.
         * 
         * @param string $provider_ref
         * @param string $to International phone number
         * @param string $status Delivery status
         * @param \DateTime $datetime Time of delivery
         * @param string $errorDesc Delivery error description
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function delivered($provider_ref, $to, $status, $datetime=null, $errorDesc='') {
            
            if (empty($provider_ref) || empty($to) || empty($status)) {
                trigger_error("Arguments missing", E_USER_WARNING);
                return false;
            }
                        
            // Get all missing with given reference
            $select = "SELECT `missing_id`, `missing_mobile_country`, `missing_mobile`  
                       FROM `missing` 
                       WHERE `sms_provider_ref` = '".$provider_ref."';";

            $result = DB::query($select);
            if(DB::isEmpty($result)) { 
                trigger_error("Missing not found", E_USER_WARNING);
                return false;
            }

            while($row = $result->fetch_assoc()) {

                $code = Locale::getDialCode($row['missing_mobile_country']);
                $number = $this->accept($code).$row['missing_mobile'];

                if(ltrim($number,'0') === ltrim($to,'0')) {
                    
                    $delivered = isset($datetime) ? "FROM_UNIXTIME({$datetime->getTimestamp()})" : "NULL";

                    $update = "UPDATE `missing` 
                               SET `sms_delivery` = $delivered, `sms_error` = '".(string)$errorDesc."'
                               WHERE `missing_id` = {$row['missing_id']}";

                    $res = DB::query($update);
                    if(!$res) {
                        trigger_error("Failed execute [$update]: " . DB::error(), E_USER_WARNING);                
                        return false;
                    }// if
                }
                
            }
            return true;

        }// delivered
        
	
    }// AbstractProvider
