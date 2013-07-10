<?php

    /**
     * File containing: Sveve class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 13. June 2013, v. 1.00
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;

    /**
     * Sveve class
     * 
     * @package 
     */
    class Sveve implements Provider 
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
         *
         * @since 13. June 2013, v. 7.60
         * 
         */
        public function __construct($user='')
        {
            $this->account = $this->newConfig($user);
        }// __construct

        public function newConfig($user='')
        {
            return array
            (
                "user" => $user
            );
        }// newConfig
        
        public function send($to, $from, $message)
        {
            // Create SMS provider url
            $smsURL = utf8_decode
            (
                  'http://www.sveve.no/SMS/SendSMS'
                . '?user='.$this->account['user']
                . '&to='.$to
                . '&from='.$from
                . '&msg='.$message
            );
            
            // Start request
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $smsURL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($curl);

            ## INIT XML
            $res = substr($res, strpos($res, '<sms>'));
            $response = $this->_SVEVESMS_XML2Array($res);
            $response = $response['response'];
            
            if(isset($response['msg_ok_number']) && is_numeric($response['msg_ok_number']) && $response['msg_ok_number']>0)
            {
                // Get first id (only one message is sent)
                return \reset($response['ids']);
            }
            else
            {
                return $response['errors'];
            }
            
        }// send
	
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
