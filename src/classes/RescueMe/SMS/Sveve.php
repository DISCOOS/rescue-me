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
    class Sveve extends AbstractProvider implements Callback
    {
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
        
        
        protected function _send($from, $to, $message, $account)
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
                
        
        private function errors($errors) {
            $messages = array();
            foreach($errors as $error) {
                if(isset($error['fatal'])) {
                    $error['number'] = Provider::FATAL;
                    $error['message'] = $error['fatal'];
                }
                $messages[] = $error['number'].":".$error['message'];
            }
            $this->error['code'] = Provider::FATAL;
            $this->error['message'] = implode("\n", $messages);
            return false;
        }
        
        public function handle($params) {
            
            assert_isset_all($params,array('id','number','status'));
            
            $this->delivered($params['id'], $params['number'], $params['status'], new \DateTime(),
                    (isset($params['errorDesc']) ? $params['errorDesc'] : ''));
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
