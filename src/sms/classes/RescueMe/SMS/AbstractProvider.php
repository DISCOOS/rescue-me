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
         * @param int|User $user User
         * @param string $country ISO country code
         * @param string $to Recipient phone number without dial code
         * @param string $message Message text
         * 
         * @return mixed|array Message id if success, FALSE otherwise.
         */
        public function send($user, $country, $to, $message)
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

            $from = $this->getSenderID($user, $country);

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

        protected function getSenderID($user, $code) {
            $id = ($user instanceof User ? $user->id : $user);
            $default = Properties::get(Properties::SMS_SENDER_ID, $id);
            if(in_array(Properties::SMS_SENDER_ID_COUNTRY,$this->uses())) {
                $json = json_decode(Properties::get(Properties::SMS_SENDER_ID_COUNTRY, $id), true);
                if(isset($json[$code])) {
                    // Select next id
                    $ids = preg_split('/,/', $json[$code]);
                    $size = sizeof($ids);
                    if(!isset($_SESSION['SENDER_ID_NEXT']) || ($index = $_SESSION['SENDER_ID_NEXT']) >= $size) {
                        $index = 0;
                        $_SESSION['SENDER_ID_NEXT'] = $index + 1;
                    } else {
                        $_SESSION['SENDER_ID_NEXT']++;
                    }
                    $default = $ids[$index];
                }
            }
            return trim($default);
        }
        
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
         * @param string $error Delivery error description
         * @param string $plnm Standard MCC/MNC tuple
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function delivered($reference, $to, $status, $datetime=null, $error='', $plnm='') {

            $context['params'] = func_get_args();
            if(empty($reference) || empty($to) || empty($status)) {
                return $this->critical("One or more required arguments are missing", $context);
            }

            // Get all sms messages with given reference and update message and mobile states
            $delivered = isset($datetime) ? "FROM_UNIXTIME({$datetime->getTimestamp()})" : "NULL";
            $filter = "`message_provider`='%s' AND `message_provider_ref` = '%s'";
            $filter = sprintf($filter, DB::escape(get_class($this)), $reference);
            $res = DB::select('messages', array('mobile_id', 'message_id'), $filter);

            if(DB::isEmpty($res) === FALSE) {

                while($row = $res->fetch_assoc()) {

                    // Update message state
                    $values = prepare_values(
                        array('message_delivered', 'message_provider_status', 'message_provider_error'),
                        array($delivered, $status, $error)
                    );

                    $filter = sprintf("`message_id`=%s", $row['message_id']);
                    if(DB::update('messages', $values, $filter)) {
                        Logs::write(Logs::SMS, LogLevel::INFO, "SMS $reference is delivered");
                    } else {
                        $context['values'] = $values;
                        $context['filter'] = $filter;
                        $this->critical(
                            "Failed to update SMS delivery status for message " . $row['message_id'], $context
                        );
                    }// if

                    // Update mobile state
                    $values = prepare_values(
                        array('sms_delivered', 'mobile_network_code'),
                        array($delivered, $plnm)
                    );

                    $filter = sprintf("`mobile_id`=%s", $row['mobile_id']);
                    if(DB::update('mobiles', $values, $filter)) {
                        Logs::write(Logs::SMS, LogLevel::INFO, "SMS $reference is delivered");
                    } else {
                        $context['values'] = $values;
                        $context['filter'] = $filter;
                        $this->critical(
                            "Failed to update SMS delivery status for mobile " . $row['mobile_id'], $context
                        );
                    }// if

                }
            } else {
                Logs::write(Logs::SMS, LogLevel::WARNING, "No SMS with reference $reference found");
            }

            return true;

        }// delivered
        
        
    }// AbstractProvider
