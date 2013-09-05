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
        private static $countries;
        
        /**
         * Get ISO2 Country Code from locale
         * 
         * @param string $locale Locale
         * 
         * @return array
         */
        public static function getCountryCode($locale) {
            $code = split("[_-]", $locale);
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
            
            $code = Properties::get(Properties::SYSTEM_COUNTRY);
            
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
            
            if(defined('DEFAULT_COUNTRY')) {
                return DEFAULT_COUNTRY;
            }
            
            $locale = self::getDefaultLocale();
            
            if($locale) {
                return self::getCountryCode($locale);
            }
            
            return false;
            
        }        
                
        
        
        
        /**
         * Get default locale (immutable)
         * 
         * @return boolean|string
         */
        public static function getDefaultLocale() {
            
            $locale = false;
            
            if(!isset($locale) && extension_loaded("intl")) {
                $locale = \locale_get_default();
            }
            
            return $locale;
            
        }        
                
        /**
         * Check if ISO2 Country Code is accepted
         * 
         * @param string $code
         * 
         * @return boolean
         */
        public static function accept($code) {
            
            return (self::getCountryInfo($code) !== false);
            
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
         * @return string Country $code ISO2 country code
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
         * @param string $code ISO country code
         * 
         * @return array Country|ies information
         */
        public static function getCountryInfo($code=false)
        {
            if(!isset(self::$countries)) {
                self::$countries = require 'countries.php';
            }
            
            if($code) {
                return isset(self::$countries[$code]) ? self::$countries[$code] : false;
            }
            
            return self::$countries;
        }

    }// Locale
