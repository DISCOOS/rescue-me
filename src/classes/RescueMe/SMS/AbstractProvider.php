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
    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;
    use \RescueMe\Log\Logger;
    use \RescueMe\Properties;
    use \RescueMe\AbstractUses;
    
    

    /**
     * AbstractProvider class
     * 
     * @package 
     */
    abstract class AbstractProvider extends AbstractUses implements Provider, Status
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
         * Constructor
         *
         * @param string $uses 
         *
         * @since 29. September 2013
         * 
         */
        public function __construct($uses=Properties::SMS_SENDER_ID)
        {
            parent::__construct($uses);
        }        
        
        
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
         * @param boolean $value Return value
         */
        protected function exception(\Exception $e, $value = false) {
            $this->error['code'] = $e->getCode();
            $this->error['message'] = Logger::toString($e);
            
            Logs::write(
                Logs::SYSTEM, 
                LogLevel::ERROR, 
                $e->getMessage(), 
                $this->error
            );
            
            return $value;
        }
        
        
        /**
         * Set fatal error
         * @param string $message
         */
        protected function fatal($message) {
            $this->error['code'] = Provider::FATAL;
            $this->error['message'] = $message;
            Logs::write(
                Logs::SYSTEM, 
                LogLevel::CRITICAL, 
                $message, 
                $this->error
            );            
            
            return false;
        }
        
        
        /**
         * Set critical error
         * @param string $message
         */
        protected function critical($message, $context = array()) {
            Logs::write(
                Logs::SYSTEM, 
                LogLevel::CRITICAL, 
                $message,
                $context
            );            
            
            return false;
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
            
            if(($code = \RescueMe\Locale::getDialCode($country)) === FALSE)
            {
                return $this->fatal("Failed to get country dial code [$country]");
            }               
                
            if(($code = $this->accept($code)) === FALSE) {
                return $this->fatal("SMS provider does not accept country dial code [$code]");
            }
            
            if(($account = $this->validateConfig($this->config())) === FALSE) {
                return $this->fatal("SMS provider configuration is invalid");
            }
            
            $id = $this->_send($from, $code.$to, $message, $account);
            
            if(is_string($id)) {
                $id = trim($id);
            }
                
            $context = prepare_values(
                array('from','to', 'message'), 
                array($from, $code.$to, $message)
            );
            
            if($id === FALSE) {
                $context['error'] = $this->error();
                Logs::write(Logs::SMS, LogLevel::ERROR, "Failed to send message to $code$to", $context);
            } else {
                Logs::write(Logs::SMS, LogLevel::INFO, "SMS sent to $code$to. Reference is $id.", $context);
            }
            
            return $id;
            
        }// send
        
        
        /**
         * Validate account
         * @param \RescueMe\Configuration $config Account [optional, null - use current
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function validate($config = null) {
            
            $valid = false;
            
            if(isset($config) === FALSE){
                $config = $this->config();
            }
            
            $valid = ($account = $this->validateConfig($config)) !== FALSE;
            
            if($valid) {
                $valid = $this->validateAccount($account);
            }
            
            return $valid;
            
        }
        
        
        /**
         * Validate configuration
         * 
         * @param array $config Provider configuration
         * 
         * @return boolean Parameters if success, FALSE otherwise.
         */
        protected function validateConfig($config) {
            
            if(isset($config) === FALSE){
                $config = $this->config();
            }
            
            foreach($config->params() as $property => $default) {
                if($config->required($property) && empty($default)) {
                    return false;
                }
            }
            
            return $config->params();
            
        }
        
        
        /**
         * Validate account with provider
         * 
         * @param array $config Provider configuration
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        protected abstract function validateAccount($config);    

        
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
         * @param string $reference
         * @param string $to International phone number
         * @param string $status Delivery status
         * @param \DateTime $datetime Time of delivery
         * @param string $errorDesc Delivery error description
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function delivered($reference, $to, $status, $datetime=null, $errorDesc='') {
                        
            if(empty($reference) || empty($to) || empty($status)) {
                $context['params'] = func_get_args();
                return $this->critical("One or more required arguments are missing", $context);
            }
                        
            // Get all missing with given reference
            $select = "SELECT `missing_id`, `missing_mobile_country`, `missing_mobile`  
                       FROM `missing` 
                       WHERE `sms_provider` = '".DB::escape(get_class($this))."' AND `sms_provider_ref` = '".$reference."';";
            
            $result = DB::query($select);
            if(DB::isEmpty($result)) { 
                $context = array('sql' => $select);
                return $this->critical("Found no missing associated with SMS reference $reference", $context);
            }

            while($row = $result->fetch_assoc()) {

                $code = Locale::getDialCode($row['missing_mobile_country']);
                $number = $this->accept($code).$row['missing_mobile'];

                if(ltrim($number,'0') === ltrim($to,'0')) {
                    
                    $delivered = isset($datetime) ? "FROM_UNIXTIME({$datetime->getTimestamp()})" : "NULL";

                    $update = "UPDATE `missing` 
                               SET `sms_delivery` = $delivered, `sms_error` = '".(string)$errorDesc."'
                               WHERE `missing_id` = {$row['missing_id']}";

                    if(DB::query($update) === FALSE) {
                        $context = array('sql' => $update);
                        $this->critical("Failed to update SMS delivery status for missing " . $row['missing_id'], $context);
                    }// if
                }
                
            }
            return true;

        }// delivered
        
        
    }// AbstractProvider
