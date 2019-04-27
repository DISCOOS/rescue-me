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
    
    use Closure;
    use RescueMe\AbstractModule;
    use RescueMe\Configuration;
    use \RescueMe\DB;
    use RescueMe\DBException;
    use \RescueMe\Locale;
    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;
    use \RescueMe\Properties;


    /**
     * AbstractProvider class
     * 
     * @package 
     */
    abstract class AbstractProvider extends AbstractModule implements Provider, Status {

        /**
         * User id for with given configuration
         * @var int
         */
        protected $user_id;


        /**
         * Constructor
         *
         * @param int $user_id User id associated with given configuration
         * @param Configuration $config Configuration
         * @param mixed $uses Uses (optional, default - Properties::SMS_SENDER_ID)
         *
         * @since 29. September 2013
         *
         */
        protected function __construct($user_id, $config, $uses = Properties::SMS_SENDER_ID)
        {
            parent::__construct($config, $uses, Logs::SMS);
            $this->user_id = $user_id;
        }

        /**
         * Send SMS message to given number.
         *
         * @param string $code ISO country code
         * @param string $number Recipient phone number without dial code
         * @param string $text SMS message text
         * @param string $client_ref Client reference (only used if provider supports it)
         * @param $on_error Closure that returns string logged with error message
         *
         * @return bool|array Provider message references, array of message ids, FALSE on failure.
         * @throws DBException
         */
        public function send($code, $number, $text, $client_ref = null, $on_error = null)
        {
            // Prepare
            unset($this->error);
            
            if(($code = Locale::getDialCode($code)) === FALSE) {
                return $this->fatal(
                    sentence(array(
                        sprintf(T_('Failed to get country dial code %s'), $code),
                        is_null($on_error) ? '' : call_user_func($on_error))
                    )
                );
            }               
                
            if(($code = $this->accept($code)) === FALSE) {
                return $this->fatal(
                    sentence(array(
                        sprintf(T_('SMS provider does not accept country dial code %s'), $code),
                        is_null($on_error) ? '' : call_user_func($on_error))
                    )
                );
            }

            $account = $this->validateRequired($this->getConfig());
            
            if($account === FALSE) {
                return $this->fatal(
                    sentence(array(
                        T_('SMS provider configuration is invalid'),
                        is_null($on_error) ? '' : call_user_func($on_error))
                    )
                );
            }

            $sender = $this->getSenderID($this->user_id, $code);
            $recipient = $code.$number;

            $references = $this->_send($sender, $recipient, $text, $account);
            
            $context = prepare_values(
                array('sender', 'recipient', 'text'),
                array($sender, $recipient, $text)
            );
            
            if($references === FALSE) {
                return $this->error(
                    sentence(array(
                        sprintf(T_('Failed to send SMS to %s'), $recipient),
                        is_null($on_error) ? '' : call_user_func($on_error))
                    ),
                    $context
                );
            }

            $this->info(sentence(array(
                sprintf(T_('SMS sent to %s'), $recipient),
                sprintf(T_('References are %s'), implode(', ',$references)))),
                $context
            );
            
            return $references;
            
        }// send

        private function getSenderID($user_id, $code) {
            $default = Properties::get(Properties::SMS_SENDER_ID, $user_id);
            if(in_array(Properties::SMS_SENDER_ID_COUNTRY,$this->uses())) {
                $json = json_decode(Properties::get(Properties::SMS_SENDER_ID_COUNTRY, $user_id), true);
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
         * @return bool|array Provider message references, FALSE on failure
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
         * @throws DBException
         */
        public function delivered($reference, $to, $status, $datetime=null, $error='', $plnm='') {

            $context['params'] = func_get_args();
            if(empty($reference) || empty($to) || empty($status)) {
                return $this->critical(
                    T_('One or more required arguments are missing'),
                    $context
                );
            }

            // Get all sms messages with given reference and update message and mobile states
            $delivered = isset($datetime) ? DB::timestamp($datetime->getTimestamp()) : "NULL";
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
                        Logs::write(Logs::SMS, LogLevel::INFO,
                            sprintf(T_('SMS %1$s is %2$s%3$s'), $reference,
                                is_bool($status)
                                ? $status
                                    ? T_('delivered')
                                    : T_('not delivered')
                                : $status,
                                $error !== '' ? sprintf(' %s ', T_('with error')) . $error : ''
                            ));
                    } else {
                        $context['values'] = $values;
                        $context['filter'] = $filter;
                        $this->critical(
                            sprintf(T_('Failed to update SMS delivery status for message %s'),
                                $row['message_id']), $context
                        );
                    }// if

                    // Update mobile state
                    $values = prepare_values(
                        array('sms_delivered', 'mobile_network_code'),
                        array($delivered, $plnm)
                    );

                    $filter = sprintf("`mobile_id`=%s", $row['mobile_id']);
                    if(!DB::update('mobiles', $values, $filter)) {
                        $context['values'] = $values;
                        $context['filter'] = $filter;
                        $this->critical(
                            sprintf(T_('Failed to update SMS delivery status for mobile %s'),
                                $row['mobile_id']), $context
                        );
                    }// if

                }
            } else {
                $this->warning(T_('No SMS with reference %s found'), $reference);
            }

            return true;

        }// delivered
        
        
    }// AbstractProvider
