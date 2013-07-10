<?php

    /**
     * File containing: SMS class
     * 
     * @copyright Copyright 2013 {@link http://www.onevoice.no One Voice AS} 
     *
     * @since 13. June 2013, v. 7.60
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
        /**
         * UMS account
         * @var array
         */
        private $account;
        
        /**
         * constructor for SMS
         *
         * @since 13. June 2013, v. 7.60
         *
         */
        public function __construct($company='', $department='', $password='')
        {
            $this->account = $this->newConfig($company, $department, $password);
            
        }// __construct

        public function newConfig($company='', $department='', $password='')
        {
            return array
            (
                "company" => $company,
                "department" => $department,
                "password" => $password
            );
        }// newConfig
        
        public function send($to, $from, $message)
        {
            try {
                
                $sms = array
                (
                    "from" => $from,
                    "text" => $message,
                    "schedule" => time()  // send immediately, to send in one hour use: time()+3600
                );

                $recipients = array($to);

                $settings = array
                (
                    "splitMessages" => true,
                    "splitFormat" => "(%d/%t)\\n",
                );

                $client = new \SoapClient("https://secure.ums.no/soap/sms/1.6/?wsdl");
                $refno = $client->doSendSMS($this->account, $sms, $recipients, $settings);
                return $refno;
                
            }
            catch(\Exception $e) 
            {
                return array(array('number' => $e->getCode() ,'message' => "$e"));
            }
            
        }// send


    }// UMS
