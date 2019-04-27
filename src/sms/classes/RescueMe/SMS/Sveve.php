<?php

    /**
     * File containing: Sveve class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;
    
    use DateTime;
    use RescueMe\Configuration;
    use RescueMe\DBException;
    use RescueMe\Properties;

    /**
     * Sveve class
     * 
     * @package 
     */
    class Sveve extends AbstractProvider implements Callback
    {
        const TYPE = 'RescueMe\SMS\Sveve';

        /**
         * Constructor
         *
         * @param int $user_id User id associated with given configuration
         * @param string $user Sveve user credentials
         * @param string $passwd Sveve user credentials (optional)
         *
         * @since 13. June 2013
         * 
         */
        public function __construct($user_id=0, $user='', $passwd='')
        {
            parent::__construct(
                $user_id,
                $this->newConfig(
                    $user_id, $user, $passwd
                ),
                array(
                Properties::SMS_SENDER_ID
            ));
        }// __construct

        
        private function newConfig($user_id=0, $user='', $passwd='')
        {
            return new Configuration(
                array(
                    "user" => $user,
                    "passwd" => $passwd,
                    Callback::PROPERTY => Callback::URL.$user_id,
                ),
                array(
                    "user" => T_('User'),
                    "passwd" => T_('Password'),
                    "callback" => T_('Callback'),
                ),
                array(
                    "user"
                )
            );
        }// newConfig
        
        
        protected function validateParameters($account)
        {
            // Create SMS provider url
            $smsURL = utf8_decode
            (
                  'https://sveve.no/SMS/AccountAdm?cmd=sms_count'
                . '&user='.$account['user']
                .(!empty($account['passwd']) ? '&passwd='.$account['passwd'] : '')
            );            
            
            // Start request
            $response = $this->invoke($smsURL);
            
            $valid = strtolower($response) !== strtolower('feil brukernavn/passord');
            if($valid === false)
            {
                $this->error['code'] = Provider::FATAL;
                $this->error['message'] = $response;
            }
            return $valid;
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
            // Create SMS provider url
            $smsURL = utf8_decode
            (
                  'https://www.sveve.no/SMS/SendSMS'
                . '?user='.$account['user']
                . '&from='.$from
                . '&to='.$to
                . '&msg='.urlencode($message)
                .(!empty($account['passwd']) ? '&passwd='.$account['passwd'] : '')
            );
            
            
            // Start request
            $response = $this->invoke($smsURL);
            
            if(isset($response['msg_ok_count']) && is_numeric($response['msg_ok_count']) && $response['msg_ok_count']>0)
            {
                return $response['ids'];
            }
            
            return $this->errors($response['errors']);
        }
        
        private function invoke($url) {
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $res = trim(curl_exec($curl));
            
            ## INIT XML?
            $xml = substr($res, strpos($res, '<sms>'));
            if($xml !== $res)
            {
                $xml = $this->_SVEVESMS_XML2Array($xml);
                $res = $xml['response'];
            }
            return $res;
        }

        /**
         * Get supported country dial code pattern.
         *
         * See {@link http://countrycode.org/ Country Codes} for more information.
         *
         * @return string Country code as
         * {@link http://www.php.net/manual/en/pcre.pattern.php PCRE pattern}
         */
        public function getDialCodePattern() {
            return '\d{1,4}';
        }

        /**
         * Check if provider accepts given telephone number.
         *
         * @param mixed $number
         *
         * @return boolean|string. Accepted code, FALSE otherwise
         */
        public function accept($number) {
            $pattern = $this->getDialCodePattern();
            if(preg_match("#$pattern#", $number) === 1) {
                return $number;
            }
            return false;
        }


        /**
         * Parse provider errors and log as fatal
         * @param $errors
         * @return bool Always FALSE
         * @throws DBException
         */
        private function errors($errors) {
            $messages = array();
            foreach($errors as $error) {
                if(isset($error['fatal'])) {
                    $error['number'] = Provider::FATAL;
                    $error['message'] = $error['fatal'];
                }
                $messages[] = $error['number'].":".$error['message'];
            }
            return $this->fatal(implode("\n", $messages));
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
            
            if(assert_isset_all($params,array('id','number','status'))) {

                $dt = new DateTime();
            
                $this->delivered(
                    $params['id'],
                    $params['number'],
                    $params['status'],
                    $dt,
                    '',
                    isset($params['errorDesc'])
                        ? $params['errorDesc']
                        : ''
                );
            }
        }
        
	
        ############################################################
        ## TRANSFORM XML TO AN ARRAY
        ############################################################
        private function _SVEVESMS_XML2Array($xml, $recursive=false){
            if (!$recursive)
                $array = simplexml_load_string($xml);
            else
                $array = $xml;

            $newArray = array();
            $array = (array) $array;
            foreach($array as $key => $value) {
                $value = (array) $value;
                if(isset($value[0]))
                    $newArray[$key] = trim($value[0]);
                else
                    $newArray[$key] = $this->_SVEVESMS_XML2Array($value, true);
            }
            
            return $newArray ;
            
        }


    }// Sveve
