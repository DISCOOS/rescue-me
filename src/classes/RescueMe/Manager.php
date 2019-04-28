<?php

    /**
     * File containing: Manager class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 27. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;

    use ReflectionException;
    use RescueMe\Device\L51D;
    use RescueMe\Email\SMTP;
    use RescueMe\SMS\Nexmo;

    /**
     * Module factory class
     */
    class Manager
    {
        /**
         * Module definition table
         */
        const TABLE = "modules";

        /**
         * Module definition table fields
         * @var array
         */
        private static $fields = array('type', 'impl', 'config', 'user_id');

        /**
         * Minimum set of modules required by RescueMe
         * @var array
         */
        private static $required = array(
            SMS\Provider::TYPE => Nexmo::TYPE,
            Email\Provider::TYPE => SMTP::TYPE,
            Device\Lookup::TYPE => L51D::TYPE,
        );

        /**
         * Install required modules if not already exist
         *
         * @param bool $init Initialize modules (default: false, potential long operation)
         * @param callable $callback Progress callback function
         *
         * @return boolean.
         * @throws DBException
         * @throws ReflectionException
         */
        public static function install($init = false, $callback = null) {
            $modules = array();

            foreach(Manager::$required as $type => $impl) {

                if(self::exists($type) === FALSE) {

                    if(!is_null($callback) && is_callable($callback)) {
                        call_user_func($callback,"Installing [$type]...");
                    }

                    /** @var Module $module */
                    $module = new $impl;
                    $config = $module->getConfig();
                    $id = self::add($type, $impl, $config->params());

                    $module = self::get($id)->newInstance();

                    // Only install supported modules
                    if($module->isSupported()) {

                        // Potentially long operation...
                        if($init) {
                            $module->init();
                        }

                        $modules[$id] = $module;

                    }

                    if(!is_null($callback) && is_callable($callback)) {
                        call_user_func($callback,"Installing [$type]...".($modules[$id] ? 'DONE' : 'SKIPPED'));
                    }
                }
                else {

                    $factory = self::get($type);
                    $module = $factory->newInstance();

                    // Only install supported modules
                    if($module->isSupported()) {

                        if(!is_null($callback) && is_callable($callback)) {
                            call_user_func($callback, "Updating [$type]...", true);
                        }

                        // Potentially long operation...
                        if($init) {
                            $module->init();
                        }

                        $modules[$factory->id] = $module;

                        if(is_null($callback) === FALSE) {
                            call_user_func($callback,"Updating [$type]...DONE");
                        }
                    }
                }

            }
            return empty($modules) ? false : true;
        }


        /**
         * Prepare user modules if not already exist
         *
         * @param integer $id User id
         * @param boolean $copy Copy system modules if true, create new otherwise.
         *
         * @return boolean TRUE if changes was made, FALSE otherwise.
         */
        public static function prepare($id, $copy = false) {
            $changed = false;

            // Get all system modules (user_id = 0)
            $factories = Manager::getAll();

            if($factories !== false) {
                /** @var Factory $factory */
                foreach($factories as $factory) {
                    if(Manager::exists($factory->type, $id) === false) {

                        $changed = true;

                        $params = $copy ? $factory->config : $factory->newConfig()->params();
                        Manager::add($factory->type, $factory->impl, $params, $id);

                    } elseif($copy) {

                        $changed = true;

                        $type = $factory->type;
                        $impl = $factory->impl;
                        $params = $factory->config;
                        $factory = Manager::get($factory->type, $id);
                        Manager::set($factory->id, $type, $impl, $params);

                    }

                }
            }
            return $changed;
        }
        
        
        /**
         * Get all module factories
         * 
         * @param integer $user_id 
         * 
         * @return boolean|Factory
         */
        public static function getAll($user_id = 0)
        {
            $res = DB::select(self::TABLE, "*", "`user_id`=$user_id");
            
            if(DB::isEmpty($res)) return false;
            
            $factories = array();
            while ($row = $res->fetch_assoc()) {
                $factory = new Factory($row);
                $factories[$row['module_id']] = $factory;
            }
            
            return empty($factories) ? false : $factories;
            
        }// getAll
        
        
        /**
         * Get module factory filter
         * 
         * @param mixed $type Module id or type
         * @param mixed $user_id User id
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
         * @param integer $user_id (optional, if null, check system module)
         * 
         * @return boolean
         */
        public static function exists($type, $user_id = null) 
        {
            $filter = Manager::filter($type, $user_id);
            
            $res = DB::select(self::TABLE, "module_id", $filter);
            
            return DB::isEmpty($res) === false;
        }


        /**
         * Get module factory
         *
         * @param mixed $type Module id or type
         * @param integer $user_id
         *
         * @return Factory|boolean
         * @throws DBException
         */
        public static function get($type, $user_id=null)
        {
            $res = DB::select(self::TABLE, "*", Manager::filter(ltrim($type,"\\"), $user_id));
            
            if(DB::isEmpty($res)) return false;

            $config = $res->fetch_assoc();
            
            return new Factory($config);
        }


        /**
         * Set module configuration
         *
         * @param integer $id Module id
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         *
         * @return boolean TRUE if success, FALSE otherwise.
         * @throws DBException
         */
        public static function set($id, $type, $impl, $config)
        {
            // Enforce namespace convention
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");

            // Sanity checks
            assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config));

            $values = prepare_values(Manager::$fields, array($type, $impl, json_encode($config)));

            if(self::exists($id)) {
                $res = DB::update(self::TABLE, $values, Manager::filter($id, null));
            }
            else {
                $res = DB::insert(self::TABLE, $values);
            }

            return ($res === TRUE || is_numeric($res) && $res > 0);

        }// set


        /**
         * Verify module configuration
         * 
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         *
         * @return boolean|string TRUE if success, message otherwise.
         */
        public static function verify($type, $impl, $config)
        {
            // Enforce namespace convention
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");
            
            // Sanity checks
            try
            {
                assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config));
            }
            catch(\Exception $e)
            {
                return $e->getMessage();
            }
            
            $module = prepare_values(Manager::$fields, array($type, $impl, json_encode($config)));
            
            $module['module_id'] = 0;
            
            $factory = new Factory($module);
            
            $instance = $factory->newInstance();
            
            $valid = $instance === FALSE ? sprintf(T_('Failed to create instance of module [%1$s]'), $impl) : TRUE;
            
            if($valid === TRUE && $instance->validate() === FALSE) {
                $valid = $instance->last_error_message();
            }
            
            return $valid;
            
        }// verify
        
        
        /**
         * Add module configuration
         * 
         * @param string $type Module type name
         * @param string $impl Module implementation name
         * @param array $config Module construction arguments as (name=>value) pairs
         * @param integer $user_id
         * 
         * @return integer Module id if success, FALSE otherwise.
         */
        public static function add($type, $impl, $config, $user_id = 0)
        {
            // Enforce namespace convention
            $type = ltrim($type,"\\");
            $impl = ltrim($impl,"\\");
            
            // Sanity checks
            assert_types(array('string'=>$type,'string'=>$impl,'array'=>$config, 'integer'=>$user_id));
            
            $values = prepare_values(Manager::$fields, array($type, $impl, json_encode($config), $user_id));
                
            return DB::insert(self::TABLE, $values);
            
        }// set


    }// Manager
