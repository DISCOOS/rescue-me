<?php

    /**
     * File containing: SMS class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}  
     *
     * @since 13. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
     */
    
    namespace RescueMe\SMS;
    
    use DateTime;
    use DateTimeZone;
    use Exception;
    use RescueMe\Configuration;
    use RescueMe\DBException;
    use RescueMe\Properties;
    use SoapClient;


    /**
     * SMS class
     * 
     * @package 
     */
    class UMS extends AbstractProvider implements Check
    {
        const TYPE = 'RescueMe\SMS\UMS';

        const WDSL_URL = "https://secure.ums.no/soap/sms/1.6/?wsdl";

        /**
         * Constructor
         *
         * @param int $user_id User id associated with given configuration
         * @param string $company UMS company id
         * @param string $department UMS user id
         * @param string $password UMS webservice password
         *
         * @since 13. June 2013
         */
        public function __construct($user_id=0, $company='', $department='', $password='')
        {
            parent::__construct(
                $user_id, /* Not used */
                $this->newConfig(
                    $user_id, $company, $department, $password
                ),
                array(
                Properties::SMS_SENDER_ID
            ));

        }// __construct
        
        
        private function newConfig($company='', $department='', $password='')
        {
            return new Configuration
            (
                array(
                    "company" => $company,
                    "department" => $department,
                    "password" => $password
                ),
                array(
                    "company" => T_('Company ID'),
                    "department" => T_('Department ID'),
                    "password" => T_('Password')
                ),
                array(
                    "company", 
                    "department", 
                    "password"
                )
            );
        }// newConfig


        protected function validateParameters($account)
        {
            try {
                
                $client = new SoapClient(UMS::WDSL_URL);
                
                // Perform dummy-check. Will fail with SoapException if credentials does not match
                $client->doGetStatus($account, 0);
                
            }
            catch(Exception $e)
            {
                if('Reference not found.' !== $e->getMessage())
                {
                    return $this->exception($e);                    
                }
            }
            
            return true;
        }


        /**
         * Actual send implementation
         *
         * @param string $from Sender
         * @param string $to Recipient international phone number
         * @param string $message Message text
         * @param string $client_ref Client reference (only used if provider supports it)
         * @param array $account Provider configuration
         * @return bool|array Provider message references, FALSE on failure
         * @throws DBException
         */
        protected function _send($from, $to, $message, $client_ref, $account)
        {
            try {
                
                $sms = array
                (
                    "from" => $from,
                    "text" => $message,
                    "schedule" => time()
                );
                
                $recipients = array($to);

                $client = new SoapClient(UMS::WDSL_URL);
                
                $refno = $client->doSendSMS($account, $sms, $recipients);
                
                return array($refno);
                
            }
            catch(Exception $e)
            {
                return $this->exception($e);
            }
            
        }// send
        
        public function getDialCodePattern() {
            return '\d{1,4}';
        }
        
        
        public function accept($code) {
            $pattern = $this->getDialCodePattern();
            if(preg_match("#$pattern#", $code) === 1) {
                return sprintf("%04d",$code);
            }
            return false;
        }        
        
                
        public function request($reference, $number)
        {
            try {
                
                $client = new SoapClient(UMS::WDSL_URL);
                
                $result = $client->doGetStatus($this->config->params(), $reference);
                
                $checked = false;

                foreach($result as $status) {

                    switch($status->queueStatus) {
                        case 'delivered':

                            // This is a workaround for strange UTC timezone behavior
                            $timezone = new DateTimeZone("UTC");
                            $datetime = DateTime::createFromFormat(DateTime::W3C, $status->deliveredToRecipient, $timezone);
                            $datetime->setTimestamp($datetime->getTimestamp()-$datetime->getOffset());

                            $this->delivered($reference, $status->sentTo, true, $datetime);

                            break;

                        default:

                            $this->delivered($reference, $status->sentTo, $status->queueStatus, null, '', $status->errorMessage);

                            break;
                    }

                    $checked = (ltrim($number,'0') === ltrim($status->sentTo,'0'));

                }
            }
            catch(Exception $e)
            {
                return $this->exception($e);
            }
                

            return $checked;
        }
        

    }// UMS
