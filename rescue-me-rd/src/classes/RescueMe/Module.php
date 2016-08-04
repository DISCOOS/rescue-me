<?php

    /**
     * File containing: Module class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 27. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;

    /**
     * Module class
     */
    class Module
    {
        const TABLE = "modules";
        const FILTER = "`type`='%1\$s'";
        
        private static $fields = array('type', 'impl', 'config');
        
        public $type;
        public $impl;
        public $config;
        
        
        private function __construct($module)
        {
            $this->type = $module['type'];
            $this->impl = $module['impl'];
            $this->config = json_decode($module['config'], true);
        }
        
        
        public static function getAll()
        {
            $res = DB::select(self::TABLE);
            
            if(DB::isEmpty($res)) return false;
            
            $modules = array();
            while ($row = $res->fetch_assoc()) {
                $module = new Module($row);
                $modules[$row['module_id']] = $module;
            }
            
            return $modules;
            
        }// getAll
        
        
        public static function exists($type) 
        {
            $res = DB::select(self::TABLE, "*", DB::prepare(self::FILTER,$type));
            
            return !DB::isEmpty($res);
        }
        
        
        public static function get($type)
        {
            $res = DB::select(self::TABLE, "*", DB::prepare(self::FILTER,$type));
            
            if(DB::isEmpty($res)) return false;

            $module = $res->fetch_assoc();
            
            return new Module($module);
        }
        
        
        /**
         * Module definition name
         * 
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         * @param boolean $construct Create module instance on success (optional, default: false).
         * 
         * @return mixed TRUE (or module instance) if success, FALSE otherwise. 
         */
        public static function set($type, $impl, $config, $construct=false)
        {
            // Sanity checks
            assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config));
            
            $values = prepare_values(self::$fields, array($type, $impl, json_encode($config)));
                
            if(self::exists($type)) {
                
                $res = DB::update(self::TABLE, $values, DB::prepare(self::FILTER,$type));
            } 
            else {
                
                $res = DB::insert(self::TABLE, $values);
            }
            
            if($res === TRUE || is_numeric($res) && $res > 0){
                
                return $construct ? new Module(array("type" => $type, "impl"  => $impl, "config"  => $config)) : true;
            }
            
            return false;
            
        }// set
        
        
        /**
         * Create module instance.
         * 
         * @param string $impl
         * @param array $config
         * @return object Module instance
         */
        public function newInstance() {
            
            $reflect  = new \ReflectionClass($this->impl);
            $invoke = array($reflect,'newInstance');
            return call_user_func_array($invoke,  $this->config);
        }
        

    }// Module
