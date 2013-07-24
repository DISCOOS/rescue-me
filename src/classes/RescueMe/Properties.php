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
        
        const SYSTEM_LOCALE = "system.locale";
        
        /**
         * Check if property exists
         * 
         * @param string $name Property name
         * 
         * @return boolean
         */
        public static function exists($name) 
        {
            $res = DB::select(self::TABLE, "COUNT(*)", "name='$name'");
            
            return !DB::isEmpty($res);
        }        
        
        /**
         * Get value of property with given name
         * 
         * @param string $name Property name
         * @param mixed $default Default property value
         * 
         * @return mixed
         */
        public static function get($name, $default=null) {
            
            $res = DB::select(self::TABLE, "`value`", "name='$name'");

            if (DB::isEmpty($res)) return $default;
            
            $row = $res->fetch_row();
            
            return $row[0];
            
        }// get
        
        
        /**
         * Set value of property with given name
         * 
         * @param string $name
         * @param string $value
         * 
         * @return boolean TRUE if success, FALSE otherwise
         */
        public function set($name, $value) {
            
            $values = array("name" => $value);                        
            
            if(self::exists($name)) {
            
                return DB::update(self::TABLE, $values, "name='$name'") !== false;
            }
            
            return DB::insert(self::TABLE, $values) !== false;
            
        }// set

        
        /**
         * Set value of property with given name
         * 
         * @param string $name
         * @param string $value
         * 
         * @return boolean TRUE if success, FALSE otherwise
         */
        public function delete($name, $value) {
            
            return DB::delete(self::TABLE, "name='$name'");
            
        }// delete
        

    }// Properties
