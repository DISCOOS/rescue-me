<?php

    /**
     * File containing: TimeZone class
     * 
     * @copyright Copyright 2014 {@link http://www.discoos.org DISCOS Open Source Association}
     *
     * @since 08. August 2014
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    

    /**
     * TimeZone class
     * 
     * @package 
     */
    class TimeZone
    {
        /**
         * Get default timezone (immutable)
         * 
         * @return boolean|string
         */
        public static function getDefault() {
            
            if(defined('DEFAULT_TIMEZONE')) {
                return DEFAULT_TIMEZONE;
            }            
            
            return date_default_timezone_get();

        }        
        
        
        /**
         * Get current locale (mutable)
         * 
         * @return boolean|string
         */
        public static function get() {
            
            if(isset($_SESSION['timezone'])) {
                return $_SESSION['timezone'];
            }
            
            return self::getDefault();
            
        }


        /**
         * Current timezone (mutable)
         *
         * @param string $identifier Timezone identifier.
         *
         * @return boolean True if accepted, false otherwise.
         */
        public static function set($identifier) {

            if(date_default_timezone_set($identifier)) {

                $_SESSION['timezone'] = $identifier;
                setcookie('timezone', self::getOffset($identifier));

                // Reconfigure database connection
                DB::reconfigure();

                return true;
            }

            return false;
        }


        /**
         * Get timezone name
         * 
         * @param string $identifier Timezone identifier
         *
         * @return string Timezone name
         *
         */
        public static function getName($identifier) {

            // TODO: Localize timezones. The challenge is that timezones identifiers are OS-specific...
            return $identifier;

        }
        
        
        /**
         * Get timezone names
         *  
         * @return array|boolean Timezone names (identifier => name)
         */
        public static function getNames() {

            $current = new \DateTimeZone(self::get());

            $timezones = $current->listIdentifiers();

            return array_combine($timezones, $timezones);
        }
        
        
        /**
         * Check if timezone identifier is accepted
         *
         * @param string $identifier Timezone identifier
         *
         * @return boolean
         */
        public static function accept($identifier) {

            $current = new \DateTimeZone(self::get());

            return in_array($current->listIdentifiers(),$identifier);
        }


        /**
         * Get UTC offset of given identifier
         *
         * @param string $identifier Timezone identifier. Current is uses not given.
         *
         * @return string UTC offset
         */
        public static function getOffset($identifier=null) {

            if(empty($identifier)) {
                $identifier = self::get();
            }

            $now = new \DateTime('now', new \DateTimeZone($identifier));
            $mins = $now->getOffset() / 60;
            $sgn = ($mins < 0 ? -1 : 1);
            $mins = abs($mins);
            $hrs = floor($mins / 60);
            $mins -= $hrs * 60;
            return sprintf('%+d:%02d', $hrs*$sgn, $mins);

        }


    }// TimeZone
