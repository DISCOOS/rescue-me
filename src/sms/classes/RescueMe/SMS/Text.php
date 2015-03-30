<?php

    /**
     * File containing: Message class
     * 
     * @copyright Copyright 2014 {@link DISCO Open Source} 
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\SMS;
    
    use RescueMe\Locale;

    /**
     * Message class
     * 
     * @package 
     */
    class Text
    {
        /**
         * Message templates
         */
        const SMS = "sms";
        private static $messages = false;

        private static $msgids = array(
            T::ALERT_SMS,
            T::ALERT_SMS_NOT_SENT,
            T::ALERT_SMS_COARSE_LOCATION,
            T::ALERT_SMS_LOCATION_UPDATE
        );

        private static function build() {

            $messages = array();

            $locales = Locale::getLocales(DOMAIN_SMS);

            foreach($locales as $locale) {
                foreach(self::$msgids as $msgid) {
                    $messages[$locale][$msgid] = T::_($msgid, $locale);
                }
            }
            
            return $messages;
        }

        public static function getAll($locale = null) {

            if(self::$messages === false) {
                self::$messages = self::build();
            }

            if(is_null($locale)) {
                return self::$messages;
            }
            
            if(isset(self::$messages[$locale])) {
                return self::$messages[$locale];
            }
            return false;
        }
        
        
        public static function get($msgid, $locale) {
            $messages = self::getAll($locale);
            if($messages !== false) {
                return $messages[$msgid];
            }            
            return false;
        }        


    }// Message
