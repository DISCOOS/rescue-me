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

    use DateTime;
    use RescueMe\Configuration;
    use RescueMe\DBException;
    use RescueMe\User;
    use RescueMe\Properties;
    
    /**
     * Clickatell class
     * 
     * @package 
     */
    class Clickatell extends AbstractProvider implements Callback
    {
        const TYPE = 'RescueMe\SMS\Clickatell';

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
         * @param int $user_id User id associated with given configuration
         * @param string $api_id Clickatell user credentials
         * @param string $user Clickatell user credentials
         * @param string $passwd Clickatell user credentials (optional)
         *
         * @since 13. June 2013
         * 
         */
        public function __construct($user_id=0, $api_id='', $user='', $passwd='')
        {
            parent::__construct(
                $user_id,
                $this->newConfig(
                    $user_id, $api_id, $user, $passwd
                ),
                array(
                    Properties::SMS_SENDER_ID,
                    Properties::SMS_OPTIMIZE, 
                    Properties::SMS_REQUIRE
                )
            );
        }// __construct

        
        private function newConfig($user_id=0, $api_id='', $user='', $password='')
        {
            return new Configuration(
                array(
                    "api_id" => $api_id,
                    "user" => $user,
                    "password" => $password,
                    Callback::PROPERTY => Callback::URL.$user_id,
                ),
                array(
                    "api_id" => T_('API ID'),
                    "user" => T_('User'),
                    "password" => T_('Password'),
                    "callback" => T_('Callback'),
                ),
                array(
                    "user",
                    "api_id",
                    "password",                    
                )
            );
        }// newConfig


        /**
         * Validate configuration parameters.
         *
         * @param array $account Associative array of parameters
         * @return boolean TRUE if success, FALSE otherwise.
         * @throws DBException
         */
        protected function validateParameters($account)
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
                return $this->fatal(
                    "Authentication failure: {$response[1]}"
                );
            }
            return true;
        }


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
                
                // Analyse message and determine if concatenation and unicode encoding is required
                list($text, $concat, $unicode) = $this->prepare($message);
                
                // Require alpha and numeric sender id support
                $require = 1;
                $values = explode(',', Properties::get(Properties::SMS_REQUIRE, $this->user_id));
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
                    return array($response[1]);
                }
                else
                {
                    return $this->fatal
                    (
                        $response[1]
                    );
                }
            }
            else
            {
                return $this->fatal(
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

        /**
         * Handle given status
         *
         * @param mixed $params
         *
         * @return void
         * @throws DBException
         */
        public function handle($params) {
            
            // Required callback params: apiMsgId, to and status (optional: cliMsgId, timestamp, from and charge)
            if(assert_isset_all($params,array('apiMsgId','to','status'))) {
            
                // Get timestamp
                $when = isset($params['timestamp']) ? DateTime::createFromFormat('U', $params['timestamp']) : new DateTime();

                // Get delivery status flag
                $delivered = ($params['status'] === "004");

                // Status description
                $description = $this->status[$params['status']];

                // Update status
                $this->delivered($params['apiMsgId'], $params['to'], $delivered, $when, '', $description);
                
            }
        }
        
        /**
         * Prepare SMS text message
         * @param $data
         * @return array
         * @throws DBException
         */
        private function prepare($data) {
            $n = 0;
            $text = '';
            $concat = 1;
            $optimize = Properties::get(Properties::SMS_OPTIMIZE, $this->user_id);
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
