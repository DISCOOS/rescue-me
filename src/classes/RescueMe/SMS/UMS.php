<?php

    /**
     * File containing: SMS class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}  
     *
     * @since 13. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
     */
    
    namespace RescueMe\SMS;
    

    /**
     * SMS class
     * 
     * @package 
     */
    class UMS implements Provider
    {
        const WDSL_URL = "https://secure.ums.no/soap/sms/1.6/?wsdl";
        
        /**
         * UMS configuration
         * @var \RescueMe\Configuration
         */
        private $config;
        
        
        /**
         * Last error
         * @var \Exception
         */
        private $error;
        
        
        /**
         * constructor for SMS
         *
         * @since 13. June 2013
         *
         */
        public function __construct($company='', $department='', $password='')
        {
            $this->config = $this->newConfig($company, $department, $password);
            
        }// __construct
        
        
        public function config()
        {
            return clone($this->config);
        }

        private function newConfig($company='', $department='', $password='')
        {
            return new \RescueMe\Configuration
            (
                array(
                    "company" => $company,
                    "department" => $department,
                    "password" => $password
                ),
                array(
                    "company" => _("company"),
                    "department" => _("department"),
                    "password" => _("password")
                ),
                array(
                    "company", 
                    "department", 
                    "password"
                )
            );
        }// newConfig
        
        
        public function send($from, $country, $to, $message)
        {
            try {
                
                // Prepare
                unset($this->error);
                
                if(!($code = $code = \RescueMe\Locale::getDialCode($country)))
                {
                    return $this->fatal("Failed to get country dial code [$country]");
                }               
                
                if(!($code = $this->accept($code))) {
                    return $this->fatal("SMS provider does not accept recipient [$to]");
                }
                
                $number = $code.$to; 
            
                $sms = array
                (
                    "from" => $from,
                    "text" => $message,
                    "schedule" => time()  // send immediately, to send in one hour use: time()+3600
                );
                
                $recipients = array($number);

                $client = new \SoapClient(UMS::WDSL_URL);
                
                $refno = $client->doSendSMS($this->config->params(), $sms, $recipients);
                
                return $refno;
                
            }
            catch(\Exception $e) 
            {
                return $this->exception($$e);
            }
            
        }// send
        
        public function getDialCodePattern() {
            return '\d{1,4}';
        }
        
        
        public function accept($code) {
            $pattern = $this->getDialCodePattern();
            if(preg_match("#$pattern#", $code) === 1) {
                return sprintf("%04d",$code);
            }
            return false;
        }        
        
        
        public function errno()
        {
            return isset($this->error) ? $this->error['code'] : 0;
        }


        public function error()
        {
            return isset($this->error) ? $this->error['message'] : '';
        }        
        
        private function exception(\Exception $e) {
            $this->error['code'] = $e->getCode();
            $this->error['message'] = $e->getMessage();
            return false;
        }
        
        
        private function fatal($message) {
            $this->error['code'] = Provider::FATAL;
            $this->error['message'] = $message;
            return false;
        }
        


    }// UMS
