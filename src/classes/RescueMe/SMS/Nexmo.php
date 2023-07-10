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
    
    use RescueMe\Configuration;
    use RescueMe\Properties;

    /**
     * Nexmo class
     * 
     * @package 
     */
    class Nexmo extends AbstractProvider implements Callback
    {
        const TYPE = 'RescueMe\SMS\Nexmo';

        private $errorCodes = array(0=>'Delivered',
                                    1=>'Unknown',
                                    2=>'Absent Subscriber - Temporary',
                                    3=>'Absent Subscriber - Permanent',
                                    4=>'Call barred by user',
                                    5=>'Portability Error',
                                    6=>'Anti-Spam Rejection',
                                    7=>'Handset Busy',
                                    8=>'Network Error',
                                    9=>'Illegal Number',
                                    10=>'Invalid Message',
                                    11=>'Unroutable',
                                    12=>'Destination Un-Reachable',
                                    13=>'Subscriber Age Restriction',
                                    14=>'Number Blocked By Carrier',
                                    15=>'Pre-Paid Insufficient Funds',
                                    50=>'Entity Filter',
                                    51=>'Header Filter',
                                    52=>'Content Filter',
                                    53=>'Consent Filter',
                                    54=>'Regulation Error',
                                    99=>'General Error');

        private $errorDesc = array(0=>'',
            1=>'Message was not delivered and no cause could be determined.',
            2=>'Message was not delivered because handset was temporarily unavailable. Retry.',
            3=>'Number is no longer active and should be removed from your database.',
            4=>'Permanent error. Number should be removed from your database and the user must contact their network operator to remove the bar.',
            5=>'Issue relating to portability of the number. Contact the network operator to resolve it.',
            6=>"Message blocked by carrier's anti-spam filter.",
            7=>'Handset not available at the time message was sent. Retry.',
            8=>'Message failed due to network error. Retry.',
            9=>'User has requested not to receive messages from a specific service.',
            10=>'Error in message parameter, e.g., wrong encoding flag.',
            11=>'Vonage cannot find a suitable route to deliver the message.',
            12=>"Route to number cannot be found. Confirm the recipient's number.",
            13=>'Target cannot receive message due to their age.',
            14=>'Recipient should ask their carrier to enable SMS on their plan.',
            15=>'Recipient is on a prepaid plan and does not have enough credit to receive your message.',
            50=>'Message failed due to entity-id being incorrect or not provided.',
            51=>'Message failed because the header ID (from phone number) was incorrect or missing.',
            52=>'Message failed due to content-id being incorrect or not provided.',
            53=>'Message failed due to consent not being authorized.',
            54=>'Unexpected regulation error.',
            99=>'General Error');
        
        /**
         * Constructor
         *
         * @param integer $user_id RescueMe user id
         * @param string $account_key Nexmo account-key
         * @param string $account_secret Nexmo account-secret
         *
         * @since 25. March 2014
         * 
         */
        public function __construct($user_id=0, $account_key='', $account_secret='')
        {
            parent::__construct(array(
                Properties::SMS_SENDER_ID,
                Properties::SMS_REQUIRE,
                Properties::SMS_REQUIRE_UNICODE
            ));
            $this->user_id = $user_id;
            $this->config = $this->newConfig($user_id, $account_key, $account_secret);
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
                    "key" => KEY,
                    "secret" => SECRET,
                    "callback" => CALLBACK,
                ),
                array(
                    "key",
                    "secret"
                )
            );
        }// newConfig
        
        protected function validateAccount($account)
        {
            // Create SMS provider url
            $url = utf8_decode
            (
                'https://rest.nexmo.com/account/get-balance/'.$account['key'].'/'.$account['secret']
            );
            
            // Start request
            $response = $this->invoke($url);
            
            $valid = (!is_null($response));
            if($valid === false)
            {
                $this->error['code'] = Provider::FATAL;
                $this->error['message'] = _('Invalid key or secret');
            }
            return $valid;
        } // validateAccount
        
        protected function _send($from, $to, $message, $account)
        {            
            
            $from = urlencode( $from );
            $message = urlencode( $message );
            
            // Create SMS provider url
            $smsURL =  'https://rest.nexmo.com/sms/json'
                       . '?api_key='.$account['key']
                       . '&api_secret='.$account['secret']
                       . '&from='.$from
                       . '&to='.$to
                       . '&text='.$message
                       . '&status-report-req=1';

            // Require unicode message?
            if(in_array(Properties::SMS_REQUIRE_UNICODE,
                explode('|',Properties::get(Properties::SMS_REQUIRE, $this->user_id)))) {
                $smsURL .= '&type=unicode';
            }
            
            // Start request
            $response = $this->invoke($smsURL);
            
            if (isset($response['messages'][0]['status']) && 
                    $response['messages'][0]['status']==="0")
            {
                // Get first id
                return $response['messages'][0]['message-id'];
            }
            
            return $this->errors(array(array('fatal'=>utf8_encode($response['messages'][0]['error-text']),
                                       'number'=>$response['messages'][0]['status'])));
        } // _send
        
        private function invoke($url) {
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
            $res = trim(curl_exec($curl));
            
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
                
        
        private function errors($errors) {
            $messages = array();
            foreach($errors as $error) {
                if(isset($error['fatal'])) {
                    $error['message'] = $error['fatal'];
                }
                $messages[] = $error['number'].":".$error['message'];
            }
            $this->error['code'] = Provider::FATAL;
            $this->error['message'] = implode("\n", $messages);
            return false;
        }

        public function handle($params) {

            if(assert_isset_all($params,array('messageId','msisdn','status'))) {
                if($params['status'] == 'delivered') { 
                    $this->delivered(
                       $params['messageId'], 
                       $params['msisdn'], 
                       $params['status'], 
                       new \DateTime()
                    );
                } else if ($params['status'] !== "accepted") {
                    $errorDesc = '';
                    if(isset($params['err-code'])) {
                        $errorCode = (int)$params['err-code'];
                        $errorDesc = $this->errorCodes[$errorCode]." ($errorCode)";
                        if(!empty($this->errorDesc[$errorCode])) {
                            $errorDesc .= ': '.$this->errorDesc[$errorCode];
                        }
                    }
                    $this->delivered(
                        $params['messageId'], 
                        $params['msisdn'], 
                        $params['status'], 
                        new \DateTime(), 
                        $errorDesc
                    );

                }// else if
            }// if
        }// handle

    }// Nexmo
