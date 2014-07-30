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
         * Installation root
         * @var string
         */
        private $root;
        

        /**
         * Constructor
         * 
         * @param string $root Installation root
         * 
         * @since 18. August 2013
         */
        public function __construct($root)
        {
            $this->root = $root;
            
        }// __construct
        
        
        /**
         * Execute status script
         * 
         * @return array|boolean array if success, FALSE otherwise.
         * 
         */
        public function execute()
        {
            begin(STATUS);
            
            info("  Analysing [$this->root".DIRECTORY_SEPARATOR."config.php]...", BUILD_INFO, NEWLINE_NONE);
            
            // Get current database parameters?
            if(!file_exists(realpath($this->root).DIRECTORY_SEPARATOR."config.php")) {
                return error(sprintf("[%s] not found","$this->root".DIRECTORY_SEPARATOR."config.php"), false);
            }
            
            info("DONE");
            
            // Get current configuration
            $config = get_config_params($this->root);            
            
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
            
            done(STATUS);
            
            // Finished
            return $config;
            
            
        }// execute
        

    }// Status
