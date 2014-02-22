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
        
        private static $required = array("RescueMe\SMS\Provider" => "RescueMe\SMS\UMS"); 
        
        /**
         * Constructor
         * @param type $module Module definition
         */
        private function __construct($module)
        {
            $this->id = $module['module_id'];
            $this->type = ltrim($module['type'],"\\");
            $this->impl = ltrim($module['impl'],"\\");
            $this->config = json_decode(isset_get($module, 'config', array()), true);
            $this->user_id = isset_get($module,'user_id', 0);
        }
        
        
        /**
         *  Install required modules if not already exist
         * 
         * @return boolean|array Array of new Module instances, FALSE if no change.
         */
        public static function install() {
            $modules = array();
            foreach(Module::$required as $type => $impl) {
                if(self::exists($type) === FALSE) {
                    $module = new $impl;
                    $id = self::add($type, $impl, $module->config()->params());
                    $modules[$id] = self::get($id);
                }                
            }
            return empty($modules) ? false : $modules;
        }
        
        
        /**
         * Get all modules
         * 
         * @param integer $user_id
         * 
         * @return boolean|\RescueMe\Module
         */
        public static function getAll($user_id = 0)
        {
            $res = DB::select(self::TABLE, "*", "`user_id`=$user_id");
            
            if(DB::isEmpty($res)) return false;
            
            $modules = array();
            while ($row = $res->fetch_assoc()) {
                $module = new Module($row);
                $modules[$row['module_id']] = $module;
            }
            
            return empty($modules) ? false : $modules;
            
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
        public static function exists($type, $user_id = null) 
        {
            $filter = Module::filter($type, $user_id);
            
            $res = DB::select(self::TABLE, "module_id", $filter);
            
            return DB::isEmpty($res) === false;
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
         * Verify module configuration
         * 
         * @param integer $id Module id
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         * @param integer $user_id
         * 
         * @return boolean TRUE if success, message otherwise. 
         */
        public static function verify($type, $impl, $config)
        {
            // Enfore namespace convension
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");
            
            // Sanity checks
            try
            {
                assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config));
            }
            catch(\Exception $e)
            {
                return _($e->message());
            }
            
            $module = prepare_values(Module::$fields, array($type, $impl, json_encode($config)));
            
            $module['module_id'] = 0;
            
            $module = new self($module);
            
            $instance = $module->newInstance();
            
            $valid = $instance === FALSE ? _("Failed to create instance of module $impl") : TRUE;
            
            if($valid === TRUE) {
                if($instance instanceof SMS\Provider) {
                    if($instance->validate() === FALSE) {
                        $valid = $instance->error();
                    }
                }                
            }
            
            return $valid;            
            
        }// set
        
        
        
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
            
            $values = prepare_values(Module::$fields, array($type, $impl, json_encode($config)));
            
            if(self::exists($id)) {
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
         * @return Module id if success, FALSE otherwise. 
         */
        public static function add($type, $impl, $config, $user_id=0)
        {
            // Enfore namespace convension
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");
            
            // Sanity checks
            assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config, 'integer'=>$user_id));
            
            $values = prepare_values(Module::$fields, array($type, $impl, json_encode($config), $user_id));
                
            return DB::insert(self::TABLE, $values);
            
        }// set
        
        
        /**
         * Get new configuration
         * 
         * @return Configuration
         * 
         */
        public function newConfig() {
            $module = new $this->impl;
            return $module->config();
        }        
        
        
        /**
         * Create module instance.
         * 
         * Returns module instance  of FALSE on error.
         * 
         * @param boolean $empty Create empty instance if true
         * 
         * @return object|false 
         */
        public function newInstance($empty=false) {
            
            $reflect  = new \ReflectionClass($this->impl);
            
            $invoke = array($reflect,'newInstance');
            
            $config = $empty ? $this->newConfig()->params() : $this->config;
            
            return call_user_func_array($invoke, $config);
        }
        

    }// Module
