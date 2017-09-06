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
                                    12=>'Destination unreachable',
                                    13=>'Subscriber Age Restriction',
                                    14=>'Number Blocked by Carrier',
                                    15=>'Pre-Paid - Insufficent funds',
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
            parent::__construct(
                $this->newConfig(
                    $user_id, $account_key, $account_secret
                ),
                array(
                    Properties::SMS_SENDER_ID,
                    Properties::SMS_SENDER_ID_COUNTRY,
                    Properties::SMS_REQUIRE,
                    Properties::SMS_REQUIRE_UNICODE
            ));
            $this->user_id = $user_id;
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
                $this->error['code'] = Provider::FATAL;
                $this->error['message'] = T_('Invalid key or secret');
            }
            return $valid;
        } // validateAccount
        
        protected function _send($from, $to, $message, $account)
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
                       . '&status-report-req=1'
                       . '&callback='.$callbackURL;

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
            
                $this->delivered($params['messageId'], $params['msisdn'], 
                        $params['status'], new \DateTime(), 
                        (in_array($params['status'], array('delivered', 'accepted')) ? '' :
                        $this->errorCodes[(int)$params['err-code']].' ('.$params['err-code'].')'));
            }
        }
        
    }// Nexmo
