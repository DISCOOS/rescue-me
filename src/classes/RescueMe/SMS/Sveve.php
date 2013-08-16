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

    /**
     * Sveve class
     * 
     * @package 
     */
    class Sveve implements Provider, Delivery
    {
        /**
         * Sveve configuration
         * 
         * @var \RescueMe\Configuration
         */
        private $config;
        
        /**
         * Description of last error
         * @var array
         */
        private $errors;
        
        /**
         * Constructor
         *
         * @param string $user Sveve user credentials
         * @param string $passwd Sveve user credentials (optional)
         *
         * @since 13. June 2013
         * 
         */
        public function __construct($user='', $passwd='')
        {
            $this->config = $this->newConfig($user, $passwd);
        }// __construct

        
        public function config()
        {
            return clone($this->config);
        }

        
        private function newConfig($user='', $passwd='')
        {
            return new \RescueMe\Configuration(
                array(
                    "user" => $user,
                    "passwd" => $passwd
                ),
                array(
                    "user" => _("user"),
                    "passwd" => _("password")
                ),
                array(
                    "user"
                )
            );
        }// newConfig
        
        public function send($from, $country, $to, $message)
        {
            // Prepare
            unset($this->errors);
            
            if(!($code = $code = \RescueMe\Locale::getDialCode($country)))
            {
                return $this->fatal("Failed to get country dial code [$country]");
            }               
                
            if(!$this->accept($code)) {
                return $this->fatal("SMS provider does not accept recipient [$to]");
            }

            $number = $code.$to; 
            
            $account = $this->config()->params();
            
            // Create SMS provider url
            $smsURL = utf8_decode
            (
                  'https://www.sveve.no/SMS/SendSMS'
                . '?user='.$account['user']
                . '&from='.$from
                . '&to='.$number
                . '&msg='.urlencode($message)
                .(!empty($account['passwd']) ? '&passwd='.$account['passwd'] : '')
            );
                        
            // Start request
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $smsURL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($curl);

            ## INIT XML
            $res = substr($res, strpos($res, '<sms>'));
            $response = $this->_SVEVESMS_XML2Array($res);
            $response = $response['response'];
            
            if(isset($response['msg_ok_count']) && is_numeric($response['msg_ok_count']) && $response['msg_ok_count']>0)
            {
                // Get first id (only one message is sent)
                return \reset($response['ids']);
            }
            else
            {
                return $this->errors($response['errors']);
            }
            
        }// send
        
        
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
                
        
        public function errno()
        {
            return isset($this->errors) ? \count($this->errors) : 0;
        }


        public function error()
        {
            $errors = array();
            if(isset($this->errors)) {
                foreach($this->errors as $error) {
                    if(isset($error['fatal'])) {
                        return $error['fatal'];
                    } else {
                        $errors[] = $error['number'].":".$error['message'];
                    }
                }
            }
            return implode("\n", $errors);
        }        

        
        private function fatal($message) {
            $this->errors['fatal'] = $message;
            return false;
        }

        
        private function errors($errors)
        {
            $this->errors = $errors;
            return false;
        }
        
        
        public function delivered($provider_ref, $to, $status, $errorDesc='') {
            if (empty($provider_ref) || empty($to) || empty($status))
                return false;
                        
            $query = "UPDATE `missing` SET `sms_delivery` = NOW(), 
                `sms_error` = '".(string)$errorDesc."'
                WHERE `missing_mobile` = '" . $to . "' 
                AND `sms_provider_ref` = '".$provider_ref."';";
            
            $db = new \RescueMe\DB();
            $res = $db->query($query);
            if(!$res){
                trigger_error("Failed execute [$query]: " . $db->error(), E_USER_WARNING);
                return false;
            }// if
            return true;
        }// log
	
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
            
        }// _SVEVESMS_XML2Array


    }// Sveve
