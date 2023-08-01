<?php

    /**
     * File containing: Locale class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOS Open Source Association} 
     *
     * @since 23. July 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    

    /**
     * Locale class
     * 
     * @package 
     */
    class Locale
    {
        private static $current;
        private static $countries;
        
        /**
         * Get default locale (immutable)
         * 
         * @return boolean|string
         */
        public static function getDefaultLocale() {
            
            if(defined('DEFAULT_LOCALE')) {
                return DEFAULT_LOCALE;
            }            
            
            $locale = false;
            
            if(extension_loaded("intl")) {
                $locale = \locale_get_default();
            }
            
            return $locale;
            
        }        
        
        
        /**
         * Get current locale (mutable)
         * 
         * @return boolean|string
         */
        public static function getCurrentLocale() {
            
            if(isset($_SESSION['locale'])) {
                return $_SESSION['locale'];
            }
            
            $locale = false;
            
            if(extension_loaded("intl")) {
                $locale = \locale_get_default();
            }
            
            return $locale;
            
        }        
        
        
                
        /**
         * Get ISO2 language code from locale
         * 
         * @param string $locale Locale
         * 
         * @return array|false
         */
        public static function getCountryLanguage($locale) {
            $code = preg_split("#[_-]#", $locale);
            return isset($code[0]) ? $code[0] : false;
        }        
        

        /**
         * Get ISO2 code of current language
         * 
         * See <a href="http://www.icu-project.org/apiref/icu4c/uloc_8h.html#details">ICU's function uloc_getDefault</a>.
         * 
         * @return boolean|string ISO Country code, FALSE otherwise.
         */
        public static function getCurrentCountryLanguage() {
            
            if(($locale = self::getCurrentLocale()) !== FALSE) {
                return self::getCountryLanguage($locale);
            }
            
            return self::getDefaultCountryLanguage();
        }        
        

        /**
         * Get default language code (immutable)
         * 
         * @return array|false
         */
        public static function getDefaultCountryLanguage() {
            
            if(($locale = self::getDefaultLocale()) !== FALSE) {
                return self::getCountryLanguage($locale);
            }
            
            return false;
            
        }


        /**
         * Get language name
         *
         * @param string $locale ISO2 Locale code
         *
         * @return string Language name in given locale
         *
         */
        public static function getLanguageName($locale) {

            $code = self::getCountryCode($locale);
            
            if($code) {
            
                $country = self::getCountryInfo($code);

                if($country !== false) {                
                    foreach($country['language'] as $name => $match) {

                        if($locale === $match) {
                            return $name;
                        }
                    }
                }
            }
            
            return false;
            
        }


        /**
         * Get locales
         *  
         * @param boolean $supported Supported only
         * 
         * @return array Country languages (locale => name)
         */
        public static function getLocales($supported = true) {
            $locales = array('en_US');
            $countries = Locale::getCountryInfo();                
            if($countries !== FALSE) {
                foreach($countries as $country) {
                    foreach($country['language'] as $name => $locale) {
                        if($locale !== 'en_US' && ($supported === FALSE || is_dir(APP_PATH_LOCALE.$locale))) {
                            $locales[] = $locale;
                        }
                    }
                } 
            }
            return $locales;
        }
        
        
        /**
         * Get country dial code
         *  
         * @param string $code ISO country code
         * 
         * @return array Country languages (locale => name)
         */
        public static function getLanguageNames($code = false, $supported = true) {
            $languages = false;
            $countries = Locale::getCountryInfo($code);                
            if($countries !== FALSE) {
                $countries = is_array($countries) ? $countries : array($countries);
                foreach($countries as $country) {
                    foreach($country['language'] as $name => $locale) {
                        if($supported === FALSE || is_dir(APP_PATH_LOCALE.$locale) || $locale === 'en_US') {
                            $languages[$locale] = $name;
                        }
                    }
                } 
            }
            return $languages;
        }
        
        
        
        /**
         * Get ISO2 Country Code from locale
         * 
         * @param string $locale Locale
         * 
         * @return string
         */
        public static function getCountryCode($locale) {
            $code = preg_split("#[_-]#", $locale);
            $code = strtoupper(isset($code[1]) ? $code[1] : $code[0]);
            return self::accept($code) ? $code : false;
        }        
        

        /**
         * Get ISO2 code of current country 
         * 
         * See <a href="http://www.icu-project.org/apiref/icu4c/uloc_8h.html#details">ICU's function uloc_getDefault</a>.
         * 
         * @return boolean|string ISO Country code, FALSE otherwise.
         */
        public static function getCurrentCountryCode() {
            
            $code = Properties::get(Properties::SYSTEM_COUNTRY_PREFIX);
            
            if(isset($code)) {
                return $code;
            }
            
            return self::getDefaultCountryCode();
        }        
        

        /**
         * Get default country code (immutable)
         * 
         * @return boolean|string
         */
        public static function getDefaultCountryCode() {
            
            if(defined('COUNTRY_PREFIX')) {
                return COUNTRY_PREFIX;
            }
            
            $locale = self::getDefaultLocale();
            
            if($locale) {
                return self::getCountryCode($locale);
            }
            
            return false;
            
        }
        
        
        /**
         * Check if ISO2 Country Code (and associated language) is accepted
         * 
         * @param string $country Country code
         * @param mixed $language Language code
         * 
         * @return boolean
         */
        public static function accept($country, $language = false) {
            $accept = ($info = self::getCountryInfo($country)) !== false;
            
            if($accept) {
                
                if($language !== FALSE) {
                    
                    $locale = $language.'_'.$country;
                    $accept = is_dir(APP_PATH_LOCALE.$locale) || $locale === 'en_US';

                }
            }
            
            return $accept;
            
        }
        
        /**
         * Get country dial code
         *  
         * @param string $code ISO country code
         * 
         * @return string Country dial code
         */
        public static function getDialCode($code) {
            $info = Locale::getCountryInfo($code);
            return isset($info['dial_code']) ? $info['dial_code'] : false;
        }
        
        
        /**
         * Get all country dial codes
         *  
         * @return array Country dial codes
         */
        public static function getDialCodes() {
            
            $codes = array();
            foreach(self::getCountryInfo() as $code => $country)
            {
                $codes[$code] = $country['dial_code'];
            }
            return $codes;
        }        
        
        
        /**
         * Get country name
         *
         * @param string $code ISO2 Country code
         * @param boolean $phone Include dial code if TRUE
         *
         * @return string Country name
         *
         */
        public static function getCountryName($code, $phone=true) {

            $name = false;

            $country = self::getCountryInfo($code);

            if($country !== false) {
                $name = ucwords(strtolower($country['country']));
                if($phone) {

                    $dail_code = $country['dial_code'];
                    $name .=  " (" . (strlen($dail_code)<4 ? "+$dail_code" : $dail_code). ")";
                }
            }
            return $name;

        }


        /**
         * Get all country names
         * 
         * @param boolean $phone Include dial code if TRUE
         *  
         * @return array Country names
         */
        public static function getCountryNames($phone=true) {
            
            $codes = array();
            foreach(self::getCountryInfo() as $code => $country)
            {
                $name = ucwords(strtolower($country['country']));
                if($phone) {
                    $dail_code = $country['dial_code'];
                    $name .=  " (" . (strlen($dail_code)<4 ? "+$dail_code" : $dail_code). ")";
                }
                $codes[$code] = $name;
            }
            \reset($codes);
            \asort($codes);
            return $codes;
        }
        
        
        /**
         * Get locale information from ISO country code.
         * 
         * @param string|boolean $code ISO country code
         * 
         * @return array Country|ies information
         */
        public static function getCountryInfo($code=false)
        {
            $locale = self::getCurrentLocale();
            if(isset(self::$countries) === false || $locale !== self::$current) {
                
                list($domain) = set_system_locale(DOMAIN_LOCALES, $locale);
                
                self::$countries = require implode(DIRECTORY_SEPARATOR, array(APP_PATH_LOCALE, 'locales', 'locales.php'));
                
                set_system_locale($domain, $locale);
                
                self::$current = $locale;
                
            }
            
            if($code) {
                return isset(self::$countries[$code]) ? self::$countries[$code] : false;
            }
            
            return self::$countries;
        }

    }// Locale
