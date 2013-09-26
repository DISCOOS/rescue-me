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
        
        const LOCATION_DESIRED_ACC = "location.desired.accuracy";
        
        const MAP_DEFAULT_BASE = 'map.default.base';
        
        public static $basemaps = array(
            'statkart.topo2' => 'Norway Topo2', 
            'terrain' => 'Terrain', 
            'satellite' => 'Satellite'
        );
        
        const SMS_SENDER_ID = 'sms.sender.id';
        
        private static $defaults = array(
            
            Properties::SYSTEM_COUNTRY => "",
            Properties::LOCATION_MAX_AGE => "900000",
            Properties::LOCATION_MAX_WAIT => "180000",
            Properties::LOCATION_DESIRED_ACC => "100",
            Properties::MAP_DEFAULT_BASE => 'terrain',
            Properties::SMS_SENDER_ID => SMS_FROM
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
        public static function getAll($user_id=0)
        {
            $res = DB::select(self::TABLE, "*", "`user_id`=$user_id");
            
            $properties = self::getDefaults();
            
            if(!DB::isEmpty($res)) {
                while ($row = $res->fetch_assoc()) {
                    $properties[$row['name']] = $row['value']; 
                }
            }
            
            return empty($properties) ? false : $properties;
            
        }// getAll       
        
        
        /**
         * Get value editor type
         * @param string $name property name
         * @return string|boolean
         */
        public static final function type($name) {
            switch($name) {
                case self::SYSTEM_COUNTRY:
                case self::MAP_DEFAULT_BASE:
                    return "select";
                case self::SMS_SENDER_ID:
                case self::LOCATION_MAX_AGE:
                case self::LOCATION_MAX_WAIT:
                case self::LOCATION_DESIRED_ACC:
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
                case self::MAP_DEFAULT_BASE:
                    return "maps/get";
            }
            return false;
        }
        
        
        /**
         * Get value text
         * 
         * @param string $name property name
         * @param integer $user_id
         * 
         * @return string|boolean
         */
        public static final function text($name, $user_id=0) {
            $value = self::get($name, $user_id);
            switch($name) {
                case self::SYSTEM_COUNTRY:
                    return Locale::getCountryName($value);
                case self::MAP_DEFAULT_BASE:
                    return isset(self::$basemaps[$value]) ? self::$basemaps[$value] : $value;
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
         * @param integer $user_id
         * 
         * @return boolean|mixed
         */
        public static function get($name, $user_id=0) {
            
            $defaults = self::getDefaults();
            
            if(!isset($defaults[$name])) {
                return false;
            }
            
            $res = DB::select(self::TABLE, "`value`", self::filter($name, $user_id));

            if (DB::isEmpty($res)) {
                return $defaults[$name];
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
            
            if(self::exists($name, $user_id)) {
            
                $values = array("name" => $name, "value" => $value);
            
                $res = DB::update(self::TABLE, $values, self::filter($name, $user_id)) !== false;
            } 
            else {
            
                $values = array("name" => $name, "value" => $value, "user_id" => $user_id);
                
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
