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
        
        private static $fields = array('type', 'impl', 'config', 'user_id');
        
        public $id;
        
        public $type;
        
        public $impl;
        
        public $config;
        
        public $user_id;
        
        /**
         * Constructor
         * @param type $module Module definition
         */
        private function __construct($module)
        {
            $this->id = $module['module_id'];
            $this->type = ltrim($module['type'],"\\");
            $this->impl = ltrim($module['impl'],"\\");
            $this->config = json_decode($module['config'], true);
            $this->user_id = $module['user_id'];
        }
        
        
        /**
         * Get all modules
         * 
         * @param integer $user_id
         * 
         * @return boolean|\RescueMe\Module
         */
        public static function getAll($user_id=0)
        {
            $res = DB::select(self::TABLE, "*", "`user_id`=$user_id");
            
            if(DB::isEmpty($res)) return false;
            
            $modules = array();
            while ($row = $res->fetch_assoc()) {
                $module = new Module($row);
                $modules[$row['module_id']] = $module;
            }
            
            return $modules;
            
        }// getAll
        
        
        /**
         * Get module filter
         * 
         * @param mixed $type Module id or type
         * 
         * @return string
         */
        private static function filter($type, $user_id) {
            $filter = is_numeric($type) ? "`module_id`=$type" : "`type`='".DB::escape(ltrim($type,"\\"))."'";
            return isset($user_id) ? "$filter AND `user_id`=$user_id" : $filter;
        }
        
        
        /**
         * Check if module exists
         * 
         * @param mixed $type Module id or type
         * @param integer $user_id
         * 
         * @return boolean
         */
        public static function exists($type, $user_id=null) 
        {
            $res = DB::select(self::TABLE, "*", Module::filter($type, $user_id));
            
            return !DB::isEmpty($res);
        }
        
        
        /**
         * Get module definition
         * 
         * @param mixed $type Module id or type
         * @param integer $user_id
         * 
         * @return boolean
         */
        public static function get($type, $user_id=null)
        {
            $res = DB::select(self::TABLE, "*", Module::filter(ltrim($type,"\\"), $user_id));
            
            if(DB::isEmpty($res)) return false;

            $module = $res->fetch_assoc();
            
            return new Module($module);
        }
        
        
        /**
         * Set module configuration
         * 
         * @param integer $id Module id
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         * @param integer $user_id
         * 
         * @return boolean TRUE if success, FALSE otherwise. 
         */
        public static function set($id, $type, $impl, $config)
        {
            // Enfore namespace convension
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");
            
            // Sanity checks
            assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config));
            
            $values = prepare_values(self::$fields, array($type, $impl, json_encode($config)));
                
            if(self::exists($type)) {
                
                $res = DB::update(self::TABLE, $values, Module::filter($id, null));
            } 
            else {
                
                $res = DB::insert(self::TABLE, $values);
            }
            
            return ($res === TRUE || is_numeric($res) && $res > 0);
            
        }// set
        
        
        /**
         * Add module configuration
         * 
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         * @param integer $user_id
         * 
         * @return boolean TRUE if success, FALSE otherwise. 
         */
        public static function add($type, $impl, $config, $user_id=0)
        {
            // Enfore namespace convension
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");
            
            // Sanity checks
            assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config, 'integer'=>$user_id));
            
            $values = prepare_values(self::$fields, array($type, $impl, json_encode($config), $user_id));
                
            $res = DB::insert(self::TABLE, $values);
            
            return ($res === TRUE || is_numeric($res) && $res > 0);
            
        }// set
        
        
        /**
         * Get new configuration
         */
        public function newConfig() {
            $config = $this->config;
            foreach(array_keys($this->config) as $key) {
                $config[$key] = '';
            }
            return $config;
        }        
        
        
        /**
         * Create module instance.
         * 
         * @param boolean $empty Create empty instance if true
         * 
         * @return object Module instance
         */
        public function newInstance($empty=false) {
            
            $reflect  = new \ReflectionClass($this->impl);
            
            $invoke = array($reflect,'newInstance');
            
            $config = $empty ? $this->newConfig() : $this->config;
            
            return call_user_func_array($invoke, $config);
        }
        

    }// Module
