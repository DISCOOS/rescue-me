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
         * Application domains
         * @var array
         */
        private static $domains = array('common', 'locales', 'admin', 'trace', 'sms');


        /**
         * Get locales accepted by browser from request
         */
        public static function getAcceptedBrowserLocales() {

            $supported = false;

            if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

                $supported = explode(',',str_replace('-', '_', $_SERVER['HTTP_ACCEPT_LANGUAGE']));

            }
            return $supported;
        }


        /**
         * Get browser locale from request
         */
        public static function getBrowserLocale() {

            $language = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);

            return self::ensureLocale($language);

        }


        private static function ensureLocale($language, $available = true) {

            // Has country code?
            $code = preg_split("#[_-]#", $language);
            if(count($code) === 2) {
                $locale = implode('_', $code);
                return $available === false || self::isAvailable($locale) ? $locale : self::getDefaultLocale();
            }

            $language = strtolower($language);

            // Search for first match
            foreach(self::getLocales($available) as $locale) {

                $code = preg_split("#[_-]#", strtolower($locale));
                if(in_array($language, $code)) {
                    return $locale;
                }

            }

            return self::getDefaultLocale();

        }


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
            
            if(!isset($locale) && extension_loaded("intl")) {
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
            
            if(!isset($locale) && extension_loaded("intl")) {
                $locale = \locale_get_default();
            }
            
            return $locale;
            
        }        
        
        
                
        /**
         * Get ISO2 language code from locale
         * 
         * @param string $locale Locale
         * 
         * @return array
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
         * @return boolean|string
         */
        public static function getDefaultCountryLanguage() {
            
            if(($locale = self::getDefaultLocale()) !== FALSE) {
                return self::getCountryLanguage($locale);
            }
            
            return false;
            
        }        
                

        /**
         * Get country name
         * 
         * @param string $locale Locale id
         *
         * @return string Language name
         * 
         */
        public static function getLanguageName($locale) {

            list($country, ) = preg_split("#[_-]#", $locale);
            
            if($country) {
            
                $country = self::getCountryInfo($country);

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
         * Check if domain(s) are available for given locale
         *
         * @param string $locale Locale id
         * @param boolean|string|array $domains Check locale only if false, all domains if true, given domain(s) otherwise.
         *
         * @return boolean
         */
        public static function isAvailable($locale, $domains = false) {

            if($locale === 'en_US' || $domains === false && is_dir(APP_PATH_LOCALE.$locale)) {
                return true;
            }

            $available = is_dir(APP_PATH_LOCALE.$locale);

            if($available) {

                $available = true;

                if($domains === true) {
                    $domains = self::$domains;
                }
                elseif(is_string($domains)) {
                    $domains = array($domains);
                }
                foreach($domains as $domain) {

                    $filename = implode(DIRECTORY_SEPARATOR,
                        array(APP_PATH_LOCALE.$locale,'LC_MESSAGES', $domain.'.po'));

                    if(file_exists($filename) === false) {

                        $available = false;
                        break;
                    }

                }

            }
            return $available;

        }


        /**
         * Get locales
         *
         * @param boolean|string $available Available only
         * 
         * @return array Country languages (locale => name)
         */
        public static function getLocales($available = true) {
            $locales = array('en_US');
            $countries = Locale::getCountryInfo();                
            if($countries !== FALSE) {
                foreach($countries as $country) {
                    foreach($country['language'] as $locale) {
                        if($available === FALSE || self::isAvailable($locale)) {
                            $locales[] = $locale;
                        }
                    }
                } 
            }
            if($locales !== FALSE) {
                asort($locales);
            }
            return $locales;
        }


        /**
         * Get country dial code
         *  
         * @param boolean|string $code ISO country code
         * @param boolean|string $available Supported only
         *
         * @return array|boolean Country languages (locale => name)
         */
        public static function getLanguageNames($code = false, $available = true) {
            $languages = false;
            $countries = Locale::getCountryInfo($code);                
            if($countries !== FALSE) {
                $languages = array();
                $countries = is_array($countries) ? $countries : array($countries);
                foreach($countries as $country) {
                    foreach($country['language'] as $name => $locale) {
                        if($available === FALSE || self::isAvailable($locale)) {
                            $languages[$locale] = $name;
                        }
                    }
                } 
            }
            if($languages !== FALSE) {
                asort($languages);
            }
            return $languages;
        }
        
        
        
        /**
         * Get ISO2 Country Code from locale
         * 
         * @param string $locale Locale
         * 
         * @return array
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
                    
                    foreach($country['language'] as $locale) {
                        if(is_dir(APP_PATH_LOCALE.$locale) || $locale === 'en_US') {
                            $accept = true;
                            break;
                        }
                    }
                    
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
            $locale = Locale::getCountryInfo($code);
            return $locale !== false ? $locale['dial_code'] : false;
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
            
            
                    $dial_code = $country['dial_code'];
                    $name .=  " (" . (strlen($dial_code)<4 ? "+$dial_code" : $dial_code). ")";
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
                    $dial_code = $country['dial_code'];
                    $name .=  " (" . (strlen($dial_code)<4 ? "+$dial_code" : $dial_code). ")";
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
                
                self::$countries =
                    require APP_PATH_LOCALE . implode(DIRECTORY_SEPARATOR,array('locales', 'locales.php'));
                
                set_system_locale($domain, $locale);
                
                self::$current = $locale;
                
            }
            
            if($code) {
                return isset(self::$countries[$code]) ? self::$countries[$code] : false;
            }
            
            return self::$countries;
        }

    }// Locale
