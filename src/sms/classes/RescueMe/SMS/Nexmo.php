<?php

    /**
     * File containing: Nexmo class
     * 
     * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 25. March 2014
     * 
     * @author Sven-Ove Bjerkan
     */
    
    namespace RescueMe\SMS;
    
    use DateTime;
    use Psr\Log\LogLevel;
    use RescueMe\Configuration;
    use RescueMe\DBException;
    use RescueMe\Properties;

    /**
     * Nexmo class
     * 
     * @package 
     */
    class Nexmo extends AbstractProvider implements Callback
    {
        const TYPE = 'RescueMe\SMS\Nexmo';

        private $statusCodes = array(
            0 => array('name' => "Success", "description" => "The message was successfully accepted for delivery."),
            1 => array("name" => "Throttled", "description" => 'You are sending SMS faster than the account limit (see <a href="https://help.nexmo.com/hc/en-us/articles/203993598">What is the Throughput Limit for Outbound SMS?</a>).'),
            2 => array("name" => "Missing Parameters", "description" => "Your request is missing one of the required parameters: from, to, api_key, api_secret or text."),
            3 => array("name" => "Invalid Parameters", "description" => "The value of one or more parameters is invalid."),
            4 => array("name" => "Invalid Credentials", "description" => "Your API key and/or secret are incorrect, invalid or disabled."),
            5 => array("name" => "Internal Error", "description" => "An error has occurred in the platform whilst processing this message."),
            6 => array("name" => "Invalid Message", "description" => "The platform was unable to process this message, for example, an un-recognized number prefix."),
            7 => array("name" => "Number Barred", "description" => "The number you are trying to send messages to is blacklisted and may not receive them."),
            8 => array("name" => "Partner Account Barred", "description" => "Your Nexmo account has been suspended. Contact support@nexmo.com."),
            9 => array("name" => "Partner Quota Violation", "description" => "You do not have sufficient credit to send the message. Top-up and retry."),
            10 => array("name" => "Too Many Existing Binds", "description" => "The number of simultaneous connections to the platform exceeds your account allocation."),
            11 => array("name" => "Account Not Enabled For HTTP", "description" => "This account is not provisioned for the SMS API, you should use SMPP instead."),
            12 => array("name" => "Message Too Long", "description" => "The message length exceeds the maximum allowed."),
            14 => array("name" => "Invalid Signature", "description" => "The signature supplied could not be verified."),
            15 => array("name" => "Invalid Sender Address", "description" => "You are using a non-authorized sender ID in the from field. This is most commonly in North America, where a Nexmo long virtual number or short code is required."),
            22 => array("name" => "Invalid Network Code", "description" => "The network code supplied was either not recognized, or does not match the country of the destination address."),
            23 => array("name" => "Invalid Callback Url", "description" => "The callback URL supplied was either too long or contained illegal characters."),
            29 => array("name" => "Non-Whitelisted Destination", "description" => "Your Nexmo account is still in demo mode. While in demo mode you must add target numbers to your whitelisted destination list. Top-up your account to remove this limitation."),
            32 => array("name" => "Signature And API Secret Disallowed", "description" => "A signed request may not also present an api_secret."),
            33 => array("name" => "Number De-activated", "description" => "The number you are trying to send messages to is de-activated and may not receive them."),
        );

        private $deliveryCodes = array(
             0 => 'Delivered',
             1 => 'Unknown',
             2 => 'Absent Subscriber - Temporary',
             3 => 'Absent Subscriber - Permanent',
             4 => 'Call barred by user',
             5 => 'Portability Error',
             6 => 'Anti-Spam Rejection',
             7 => 'Handset Busy',
             8 => 'Network Error',
             9 => 'Illegal Number',
            10 => 'Invalid Message',
            11 => 'Unroutable',
            12 => 'Destination unreachable',
            13 => 'Subscriber Age Restriction',
            14 => 'Number Blocked by Carrier',
            15 => 'Pre-Paid - Insufficent funds',
            99 => 'General Error'
        );


        /**
         * Constructor
         *
         * @param int $user_id User id associated with given configuration
         * @param string $account_key Nexmo account-key
         * @param string $account_secret Nexmo account-secret
         *
         * @since 25. March 2014
         * 
         */
        public function __construct($user_id=0, $account_key='', $account_secret='')
        {
            parent::__construct(
                $user_id,
                $this->newConfig(
                    $user_id, $account_key, $account_secret
                ),
                array(
                    Properties::SMS_SENDER_ID,
                    Properties::SMS_SENDER_ID_COUNTRY,
                    Properties::SMS_REQUIRE,
                    Properties::SMS_REQUIRE_UNICODE
            ));
        }// __construct

        private function newConfig($user_id=0, $account_key='', $account_secret='')
        {
            return new Configuration(
                array(
                    "key" => $account_key,
                    "secret" => $account_secret,
                    Callback::PROPERTY => Callback::URL.$user_id,
                ),
                array(
                    "key" => T_('Key'),
                    "secret" => T_('Secret'),
                    "callback" => T_('Callback'),
                ),
                array(
                    "key",
                    "secret"
                )
            );
        }// newConfig
        
        protected function validateParameters($account)
        {
            // Create SMS provider url
            $url = utf8_decode
            (
                'https://rest.nexmo.com/account/get-balance/'.$account['key'].'/'.$account['secret']
            );
            
            // Start request
            $response = $this->invoke($url);
            
            $valid = !(is_null($response) || isset($response['error-code']));
            if($valid === false)
            {
                $this->fatal(T_('Invalid key or secret'));
            }
            return $valid;
        } // validateAccount

        /**
         * Provider implementation
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

            $from = urlencode( $from );
            $message = urlencode( $message );
            $callbackURL = urlencode( APP_URL.Callback::URL.$this->user_id );
            
            // Create SMS provider url
            $smsURL =  'https://rest.nexmo.com/sms/json'
                       . '?api_key='.$account['key']
                       . '&api_secret='.$account['secret']
                       . '&from='.$from
                       . '&to='.$to
                       . '&text='.$message
                       . '&client-ref='.$client_ref
                       . '&status-report-req=1'
                       . '&callback='.$callbackURL;

            // Require unicode message?
            if(in_array(Properties::SMS_REQUIRE_UNICODE,
                explode('|',Properties::get(Properties::SMS_REQUIRE, $this->user_id)))) {
                $smsURL .= '&type=unicode';
            }
            
            // Start request
            return $this->parse($this->invoke($smsURL));


        } // _send

        /**
         * Parse response
         * @param array $response
         * @return bool|array References to accepted messages, FALSE if none was accepted.
         * @throws DBException
         */
        private function parse($response) {

            $messages = isset_get($response,'messages', false);

            if(FALSE === $messages){
                return $this->fatal("Invalid response");
            }

            $references = array();

            foreach($messages as $message) {

                $status = (int)isset_get($message,'status', 99);
                $reference = isset_get($message,'message-id', false);
                if($status !== 0) {
                    $error = $this->statusCodes[$status];
                    $this->set_last($status, $error['name']);
                    $this->log(LogLevel::CRITICAL, sentences(
                        array(
                            sprintf(T_('Failed to send SMS message %s'), $reference),
                            sprintf(T_('Provider responded %s'),"{$error['name']} ($status)")
                        )),
                        $message, false);

                }
                $references[] = $reference;
            }

            return count($references) > 0 ? $references : false;
        }
        
        private function invoke($url) {
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
            $res = trim(curl_exec($curl));
            curl_close($curl);

            return json_decode($res, TRUE);
        } // invoke
        
        
        public function getDialCodePattern() {
            return '\d{1,4}';
        }
        
        
        public function accept($code) {
            $pattern = $this->getDialCodePattern();
            if(preg_match("#$pattern#", $code) === 1) {
                return $code;
            }
            return false;
        }        
                
        
        /**
         * @param mixed $params
         * @throws DBException
         */
        public function handle($params) {
            
            if(assert_isset_all($params,array('messageId','msisdn','status'))) {

                // Status 'accepted' is an intermediate state not tracked by RescueMe
                if($params['status'] !== 'accepted') {

                    // Prepare values
                    $delivered = $params['status'] === 'delivered';
                    $datetime = $delivered ? new DateTime() : null;
                    $client_ref = isset_get($params,'client-ref', false);
                    $error = $delivered
                        ? "{$this->deliveryCodes[(int)$params['err-code']]} ({$params['err-code']})"
                        : '';

                    // Update message status
                    $this->delivered(
                        $params['messageId'],
                        $params['msisdn'],
                        $delivered,
                        $datetime,
                        $client_ref,
                        $error,
                        $params['network-code']
                    );
                }
            }
        }
        
    }// Nexmo
