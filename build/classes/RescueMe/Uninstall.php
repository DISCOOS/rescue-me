<?php

    /**
     * File containing: Install class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 19. June 2013, v. 7.60
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;

    /**
     * Uninstall class
     * 
     * @package 
     */
    class Uninstall
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
         * 
         * @since 19. June 2013, v. 7.60
         *
         */
        public function __construct($root)
        {
            $this->root = $root;
            
        }// __construct
        
        
        /**
         * Execute uninstall script
         * 
         * @return mixed Config values if success (array), error message otherwise.
         * 
         */
        public function execute()
        {
            // Notify
            out("Inspecting installation in [$this->root]....", PRE);
            
            // Not found?
            if(!file_exists(realpath($this->root))) {
                return FAILED."(".NOT_FOUND.")";
            }// if
            
            // Get current configuration
            $config = file_get_contents($this->root."config.php");
            $ini = get_define_array($config, array
            (
                'SALT', 'VERSION', 'TITLE', 'SMS_FROM', 
                'DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD',
                'GOOGLE_API_KEY'
            ));
            
            // Uninstall application
            out("Uninstalling [$this->root]....", PRE);
            if(!rrmdir(realpath($this->root))) {
                return FAILED."(".RM_DIR_FAILED.")";
            }// if
            
            // Finished
            return $ini;
            
        }// execute


    }// Uninstall
