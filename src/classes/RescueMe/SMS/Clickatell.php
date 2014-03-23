<?php

    /**
     * File containing: Clickatell class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 25. September 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;

    use RescueMe\User;
    use RescueMe\Properties;
    
    /**
     * Clickatell class
     * 
     * @package 
     */
    class Clickatell extends AbstractProvider implements Callback
    {
        private $status = array(
            
            "001" => "Message unknown", 
            "002" => "Message queued",
            "003" => "Delivered to gateway",
            "004" => "Received by recipient",
            "005" => "Error with message",
            "006" => "User cancelled message delivery",
            "007" => "Error delivering message",
            "008" => "Message received by gateway",
            "009" => "Routing error",
            "010" => "Message expired", 
            "011" => "Message queued for later delivery"
        );
        

        /**
         * Constructor
         *
         * @param string $user Clickatell user credentials
         * @param string $user Clickatell user credentials
         * @param string $passwd Clickatell user credentials (optional)
         *
         * @since 13. June 2013
         * 
         */
        public function __construct($api_id='', $user='', $passwd='')
        {
            parent::__construct(
                array(
                    Properties::SMS_SENDER_ID,
                    Properties::SMS_OPTIMIZE, 
                    Properties::SMS_REQUIRE
                )
            );
            $this->config = $this->newConfig($api_id, $user, $passwd);
        }// __construct

        
        private function newConfig($api_id='', $user='', $password='')
        {
            return new \RescueMe\Configuration(
                array(
                    "api_id" => $api_id,
                    "user" => $user,
                    "password" => $password,
                    Callback::PROPERTY => Callback::URL.\RescueMe\User::currentId(),
                ),
                array(
                    "api_id" => API_ID,
                    "user" => USER,
                    "password" => PASSWORD,
                    "callback" => CALLBACK,
                ),
                array(
                    "user",
                    "api_id",
                    "password",                    
                )
            );
        }// newConfig
        
        
        protected function validateAccount($account)
        {
            $user = $account['user'];
            $api_id = $account['api_id'];
            $password = $account['password'];
            $baseurl = "http://api.clickatell.com";

            // Auth call
            $url = "$baseurl/http/auth?user=$user&password=$password&api_id=$api_id";

            // Do auth call
            $result = file($url);
            
            // Explode response (first line of the data returned)
            $response = explode(":", $result[0]);
            if($response[0] !== "OK")
            {
                return $this->errors(
                    "Authentication failure: {$response[1]}"
                );
            }
            return true;
        }

        
        protected function _send($from, $to, $message, $account)
        {
            $user = $account['user'];
            $api_id = $account['api_id'];
            $password = $account['password'];
            $baseurl = "http://api.clickatell.com";

            // Auth call
            $url = "$baseurl/http/auth?user=$user&password=$password&api_id=$api_id";

            // Do auth call
            $result = file($url);

            // Explode response (first line of the data returned)
            $response = explode(":", $result[0]);
            if($response[0] === "OK")
            {
                $sess_id = trim($response[1]);
                
                // Analyse message and determine if concatenation and unicode encoding is requried
                list($text, $concat, $unicode) = $this->prepare($message);
                
                // Require alpha and numeric sender id support
                $require = 1;
                $values = explode(',', Properties::get(Properties::SMS_REQUIRE, User::currentId()));
                if(in_array(Properties::SMS_REQUIRE_UNICODE, $values)) $require += ($unicode ? 8 : 0);
                if(in_array(Properties::SMS_REQUIRE_SENDER_ID_ALPHA, $values)) $require += 16;
                if(in_array(Properties::SMS_REQUIRE_SENDER_ID_NUMERIC, $values)) $require += 32;
                if($concat > 1 && in_array(Properties::SMS_REQUIRE_MULTIPLE, $values)) $require += 16384;
                
                // Enable callback for final and error statuses and delivery acknowledgment (if supported)
                $url = sprintf(
                    "%s/http/sendmsg?session_id=%s&from=%s&to=%s&concat=%s&text=%s&unicode=%s&req_feat=%s&callback=6&deliv_ack=1",
                    $baseurl, 
                    $sess_id,
                    $from, 
                    $to,
                    $concat,
                    $text,
                    ($unicode ? "1" : "0"),
                    $require
                );
                
                // Do sendmsg call
                $result = file($url);
                $response = explode(":", $result[0]);
                
                if($response[0] === "ID")
                {
                    return $response[1];
                }
                else
                {
                    return $this->errors
                    (
                        $response[1]
                    );
                }
            }
            else
            {
                return $this->errors(
                    "Authentication failure: {$response[1]}"
                );
            }
            
        }
        
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
                
        
        public function handle($params) {
            
            // Required callback params: apiMsgId, to and status (optional: cliMsgId, timestamp, from and charge)
            if(assert_isset_all($params,array('apiMsgId','to','status'))) {
            
                // Get timestamp
                $when = isset($params['timestamp']) ? \DateTime::createFromFormat('U', $params['timestamp']) : new \DateTime();

                // Status description
                $description = $this->status[$params['status']];

                // Update status
                $this->delivered($params['apiMsgId'], $params['to'], $params['status'], $when, $description);
                
            }
        }
        
        private function errors($message, $code = Provider::FATAL) {
            
            $this->error['code'] = $code;
            $this->error['message'] = $message;

            return false;
        }
        
        private function prepare($data) {
            $n = 0;
            $text = '';
            $concat = 1;
            $optimize = Properties::get(Properties::SMS_OPTIMIZE, User::currentId());
            $unicode = $optimize === Properties::SMS_OPTIMIZE_ENCODING && !$this->isASCII($data);
            $max = ($unicode ? 70 : 160);
            for($i = 0; $i < mb_strlen($data, 'UTF-8'); $i++) {
                $c = mb_substr($data, $i, 1, 'UTF-8');
                if($unicode){
                    $o = unpack('N', mb_convert_encoding($c, 'UCS-4BE', 'UTF-8'));
                    $c = sprintf('%04X', $o[1]);
                }
                $text .= $c;
                $n++;
                if($n === $max){
                    $n = 0;
                    $concat++;
                }
            }
            $text = ($unicode? $text : urlencode($text));
            return array($text, $concat, $unicode);
        }

        private function isASCII($string = '') {
            $num = 0;
            while(isset($string[$num])){
                if(ord($string[$num]) & 0x80){
                    return false;
                }
                $num++;
            }
            return true;
        }


    }// Clickatell
