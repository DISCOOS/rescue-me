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
    
    use RescueMe\AbstractModule;
    use RescueMe\Configuration;
    use \RescueMe\DB;
    use RescueMe\Domain\Messages;
    use \RescueMe\Locale;
    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;
    use \RescueMe\Properties;
    use RescueMe\User;


    /**
     * AbstractProvider class
     * 
     * @package 
     */
    abstract class AbstractProvider extends AbstractModule implements Provider, Status {

        /**
         * Constructor
         *
         * @param $config Configuration Configuration
         * @param mixed $uses Uses (optional, default - Properties::SMS_SENDER_ID)
         *
         * @since 29. September 2013
         *
         */
        public function __construct($config, $uses = Properties::SMS_SENDER_ID)
        {
            parent::__construct($config, $uses);
        }        
        
        
        /**
         * Send SMS message to given number.
         * 
         * @param string $from Sender
         * @param string $country International dial code
         * @param string $to Recipient phone number without dial code
         * @param string $text Message text
         * 
         * @return mixed|array Message id if success, FALSE otherwise.
         */
        public function send($from, $country, $to, $text)
        {
            // Prepare
            unset($this->error);
            
            if(($code = Locale::getDialCode($country)) === FALSE)
            {
                return $this->fatal("Failed to get country dial code [$country]");
            }               
                
            if(($code = $this->accept($code)) === FALSE) {
                return $this->fatal("SMS provider does not accept country dial code [$code]");
            }

            $account = $this->validateRequired($this->getConfig());
            
            if($account === FALSE) {
                return $this->fatal("SMS provider configuration is invalid");
            }
            
            $id = $this->_send($from, $code.$to, $text, $account);
            
            if(is_string($id)) {
                $id = trim($id);
            }

            if($id === FALSE) {
                $context['error'] = $this->error();
                Logs::write(Logs::SMS, LogLevel::ERROR, "Failed to send message to $code$to", $context);
            } else {

                // Insert into messages
                $messageId = Messages::insert(array (
                    'message_type' => Text::SMS,
                    'message_from' => $from,
                    'message_to' => $code.$to,
                    'message_subject' => '',
                    'message_data' => $text,
                    'message_state' => Provider::SENT,
                    'message_timestamp' => mysql_dt(time()),
                    'message_provider' => get_class($this),
                    'message_reference' => $id,
                    'user_id' => User::currentId()));

                // Save message id in context
                $context = array(
                    'message_id' => $messageId
                );

                Logs::write(Logs::SMS, LogLevel::INFO, "SMS sent. Reference is $id.", $context);
            }
            
            return $id;
            
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
         * @param string $reference Message reference
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
            
            $res = DB::query($select);
            
            if(DB::isEmpty($res) === FALSE) { 

                while($row = $res->fetch_assoc()) {

                    $code = Locale::getDialCode($row['missing_mobile_country']);
                    $number = $this->accept($code).$row['missing_mobile'];

                    if(ltrim($number,'0') === ltrim($to,'0')) {

                        $delivered = isset($datetime) ? "FROM_UNIXTIME({$datetime->getTimestamp()})" : "NULL";

                        $update = "UPDATE `missing` 
                                   SET `sms_delivery` = $delivered, `sms_error` = '".(string)$errorDesc."'
                                   WHERE `missing_id` = {$row['missing_id']}";

                        if(DB::query($update)) {
                            Logs::write(Logs::SMS, LogLevel::INFO, "SMS $reference is delivered");
                        } else {
                            $context = array('sql' => $update);
                            $this->critical("Failed to update SMS delivery status for missing " . $row['missing_id'], $context);
                        }// if
                    }

                }
            }
            
            return true;

        }// delivered
        
        
    }// AbstractProvider
