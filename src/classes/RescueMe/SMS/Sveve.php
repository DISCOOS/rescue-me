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
         * Sveve account
         * 
         * @var string
         */
        private $account;
        
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
            $this->account = $this->newConfig($user, $passwd);
        }// __construct

        
        public function config()
        {
            return $this->account;
        }

        
        private function newConfig($user='', $passwd='')
        {
            return array
            (
                "fields" => array(
                    "user" => $user,
                    "passwd" => $passwd
                ),
                "required" => array(
                    "user"
                ),
                "labels" => array(
                    "user" => _("user"),
                    "passwd" => _("password")
                )
            );
        }// newConfig
        
        public function send($to, $from, $message)
        {
            // Create SMS provider url
            $smsURL = utf8_decode
            (
                  'https://www.sveve.no/SMS/SendSMS'
                . '?user='.$this->account['fields']['user']
                . '&to='.$to
                . '&from='.$from
                . '&msg='.urlencode($message)
                .(!empty($this->account['fields']['passwd']) ? '&passwd='.$this->account['fields']['passwd'] : '')
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
                return $response['errors'];
            }
            
        }// send
        
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
