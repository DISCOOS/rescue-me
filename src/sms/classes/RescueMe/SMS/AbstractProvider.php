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
    use RescueMe\Domain\Messages;
    use RescueMe\Locale;
    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;
    use RescueMe\Properties;


    /**
     * AbstractProvider class
     * 
     * @package 
     */
    abstract class AbstractProvider extends AbstractModule implements Provider, SetStatus {

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
         * Test if provider supports interface \RescueMe\SMS\Check
         */
        public function supportsCheck() {
            return $this instanceof CheckStatus;
        }


        /**
         * Test if provider supports interface \RescueMe\SMS\Lookup
         */
        public function supportsLookup() {
            return $this instanceof Lookup;
        }

        /**
         * Send SMS message to given number.
         * 
         * @param string $from Sender
         * @param string $country International dial code
         * @param string $to Recipient phone number without dial code
         * @param string $text Message text
         * @param integer $userId User id
         *
         * @return integer|array Message id if success, FALSE otherwise.
         */
        public function send($from, $country, $to, $text, $userId)
        {
            $messageId = false;

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
            
            $reference = $this->_send($from, $code.$to, $text, $account);
            
            if(is_string($reference)) {
                $reference = trim($reference);
            }

            if($reference === FALSE) {
                $context['error'] = $this->error();
                Logs::write(Logs::SMS, LogLevel::ERROR, "Failed to send message to $code$to", $context);
            } else {

                // Insert into messages
                $messageId = Messages::insert(
                    array(
                        'message_type' => Text::SMS,
                        'message_from' => $from,
                        'message_to' => $code.$to,
                        'message_data' => $text,
                        'message_state' => Provider::SENT,
                        'message_provider' => get_class($this),
                        'message_reference' => $reference,
                        'user_id' => $userId
                    )
                );

                // Save message id in context
                $context = array(
                    'message_id' => $messageId
                );

                Logs::write(Logs::SMS, LogLevel::INFO, "SMS sent. Reference is $reference.", $context);
            }
            
            return $messageId;
            
        }// send
        
        /**
         * Actual send implementation
         * 
         * @param string $from Sender
         * @param string $to Recipient international phone number
         * @param string $message Message text
         * @param array $account Provider configuration
         * @return string SMS reference returned by provider
         */
        protected abstract function _send($from, $to, $message, $account);

        
        /**
         * Update SMS delivery status.
         * 
         * @param string $reference Message reference
         * @param string $to International phone number
         * @param string $status Delivery status
         * @param \DateTime $datetime Time of delivery
         * @param string $error Delivery error description
         * @return boolean Message id if success, FALSE otherwise.
         */
        public function delivered($reference, $to, $status, $datetime=null, $error=null) {
                        
            if(empty($reference) || empty($status)) {
                $context['params'] = func_get_args();
                return $this->critical("One or more required arguments are missing", $context);
            }

            $message = Messages::get($reference);

            if($message === FALSE) {

                if(is_null($error)) {
                    $message['message_status'] = 'delivered';
                    $message['message_delivered'] =
                        isset($datetime) ? "FROM_UNIXTIME({$datetime->getTimestamp()})" : "NULL";
                } else {
                    $message['message_status'] = 'error';
                    $message['message_error'] = $error;
                }

                $id = $message['message_id'];
                if(Messages::update($id, $message)) {
                    Logs::write(Logs::SMS, LogLevel::INFO, "SMS $reference is delivered");
                    return $id;
                } else {
                    /** @var array $message */
                    $this->critical("Failed to update SMS delivery status for message $id", $message);
                }
            }
            
            return false;

        }// delivered
        
        
    }// AbstractProvider
