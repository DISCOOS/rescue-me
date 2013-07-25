<?php

    /**
     * File containing: Properties class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOS Open Source Association} 
     *
     * @since 24. July 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    

    /**
     * Properties class
     * 
     * @package 
     */
    class Properties
    {
        const TABLE = "properties";
        
        const SYSTEM_COUNTRY = "system.country";
        
        const LOCATION_MAX_WAIT = "location.max.wait";
        
        const LOCATION_MAX_AGE = "location.max.age";
        
        private static $defaults = array(
            
            Properties::SYSTEM_COUNTRY => "",
            Properties::LOCATION_MAX_AGE => "900000",
            Properties::LOCATION_MAX_WAIT => "180000"
        );
        
        public static function getDefaults() {
            self::$defaults[Properties::SYSTEM_COUNTRY] = Locale::getDefaultCountryCode();
            return self::$defaults;
        }
        
        
        /**
         * Get all properties
         * 
         * @param integer $user_id
         * 
         * @return boolean|\RescueMe\Module
         */
        public static function getAll($user_id=null)
        {
            $res = DB::select(self::TABLE, "*", "`user_id`=$user_id");
            
            $properties = self::getDefaults();
            
            if(!DB::isEmpty($res)) {
                while ($row = $res->fetch_assoc()) {
                    $properties[$row['name']] = $row['value']; 
                }
            }
            
            return $properties;
            
        }// getAll       
        
        
        /**
         * Get value editor type
         * @param string $name property name
         * @return string|boolean
         */
        public static final function type($name) {
            switch($name) {
                case self::SYSTEM_COUNTRY:
                    return "select";
                case self::LOCATION_MAX_AGE:
                case self::LOCATION_MAX_WAIT:
                    return "text";
            }
            return false;
        }
        
        
        /**
         * Get value options source
         * 
         * @param string $name property name
         * @return string|boolean
         */
        public static final function source($name) {
            switch($name) {
                case self::SYSTEM_COUNTRY:
                    return "countries/get";
            }
            return false;
        }
        
        
        /**
         * Get value text
         * 
         * @param string $name property name
         * @return string|boolean
         */
        public static final function text($name) {
            $value = self::get($name);
            switch($name) {
                case self::SYSTEM_COUNTRY:
                    return Locale::getCountryName($value);
            }
            return $value;
        }
        
        
        /**
         * Check if property exists
         * 
         * @param string $name Property name
         * @param integer $user_id
         * 
         * @return boolean
         */
        public static function exists($name, $user_id=0) 
        {
            $res = DB::select(self::TABLE, "*", self::filter($name, $user_id));
            
            return !DB::isEmpty($res);
        }        
        
        /**
         * Get value of property with given name
         * 
         * @param string $name Property name
         * @param mixed $default Default property value
         * @param integer $user_id
         * 
         * @return boolean|mixed
         */
        public static function get($name, $default=null, $user_id=0) {
            
            $defaults = self::getDefaults();
            
            if(!isset($defaults[$name])) {
                return false;
            }
            
            $res = DB::select(self::TABLE, "`value`", self::filter($name, $user_id));

            if (DB::isEmpty($res)) {
                return isset($default) ? $default : $defaults[$name];
            }
            
            $row = $res->fetch_row();
            
            return $row[0];
            
        }// get
        
        
        /**
         * Set value of property with given name
         * 
         * @param string $name
         * @param mixed $value
         * @param integer $user_id
         * 
         * @return boolean TRUE if success, FALSE otherwise
         */
        public static function set($name, $value, $user_id=0) {
            
            $values = array("name" => $name, "value" => $value);
            
            if(self::exists($name)) {
            
                $res = DB::update(self::TABLE, $values, self::filter($name, $user_id)) !== false;
            } 
            else {
            
                $res = DB::insert(self::TABLE, $values) !== false;
            }
            
            return ($res === TRUE || is_numeric($res) && $res > 0);
            
            
        }// set

        
        /**
         * Set value of property with given name
         * 
         * @param string $name
         * @param int $user_id
         * 
         * @return boolean TRUE if success, FALSE otherwise
         */
        public function delete($name, $user_id=0) {
            
            return DB::delete(self::TABLE, self::filter($name, $user_id));
            
        }// delete
        
        
        /**
         * Get property filter
         * 
         * @param string $name Property name
         * @param integer $user_id User id
         * 
         * @return string
         */
        private static function filter($name, $user_id) {
            return "`name`='$name' AND `user_id`=$user_id";
        }        

    }// Properties
