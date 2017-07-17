<?php

    /**
     * File containing: Status class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 18. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    /**
     * Status class
     * 
     * @package 
     */
    class Status
    {
        /**
         * RescueMe Status parameters
         * @var array
         */
        private $params;


        /**
         * Database directory root
         * @var string
         */
        private $root;


        /**
         * Constructor
         *
         * @param string $root Database directory root
         * @param array $params Status parameters
         *
         * @since 18. August 2013
         */
        public function __construct($root, $params) {
            $this->params = $params;
            $this->root = $root;

        }// __construct


        /**
         * Execute status script
         *
         * @param array $keys Configuration keys
         *
         * @return array|boolean array if success, FALSE otherwise.
         */
        public function execute($keys)
        {
            info("  Analysing [$this->root".DIRECTORY_SEPARATOR."config.php]...", BUILD_INFO, NEWLINE_NONE);
            
            // Get current database parameters?
            if(!file_exists(realpath($this->root).DIRECTORY_SEPARATOR."config.php")) {
                return error(sprintf("[%s] not found","$this->root".DIRECTORY_SEPARATOR."config.php"), false);
            }
            
            info("DONE");
            
            // Get current configuration
            $config = get_config_params($this->root, $keys);
            
            // Print all parameters
            foreach($config as $key => $value) {
                info("  $key = $value");
            }            
            
            // Get current minify configuration
            $config = get_config_minify_params($this->root);            
            
            // Print all parameters
            foreach($config as $key => $value) {
                info("  $key = $value");
            }

            // Connect to database
            DB::instance()->connect(
                $this->params[PARAM_HOST],
                $this->params[PARAM_USERNAME],
                $this->params[PARAM_PASSWORD],
                $this->params[PARAM_DB]);

            $latest = DB::latestVersion();
            info("  DB_VERSION = $latest");

            $version = get_version($this->root);
            info("  APP_VERSION = $version");

            // Finished
            return $config;
            
            
        }// execute
        

    }// Status
