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
    class Message
    {
        private static $messages;
        
        private static function build() {
            
            $messages = array();
        
            $locales = Locale::getLocales();
            
            foreach($locales as $locale) {
                
                $messages[$locale]['ALERT_SMS_TRACE'] = T_locale(DOMAIN_SMS, $locale, 'ALERT_SMS_TRACE');
                $messages[$locale]['ALERT_SMS_NOT_SENT'] = T_locale(DOMAIN_SMS, $locale, 'ALERT_SMS_NOT_SENT');
                $messages[$locale]['ALERT_SMS_2'] = T_locale(DOMAIN_SMS, $locale, 'ALERT_SMS_2');
                $messages[$locale]['ALERT_SMS_LOCATION_UPDATE'] = T_locale(DOMAIN_SMS, $locale, 'ALERT_SMS_LOCATION_UPDATE');
                
            }
            
            return $messages;
            
        }

        public static function getAll($locale = null) {
            self::$messages = self::build();
            
            if(is_null($locale)) {
                return self::$messages;
            }
            
            if(isset(self::$messages[$locale])) {
                return self::$messages[$locale];
            }
            return false;
        }
        
        
        public static function get($locale, $msgid) {
            $messages = self::getAll($locale);
            if($messages !== false) {
                return $messages[$msgid];
            }            
            return false;
        }        


    }// Message
