<?php

    /**
     * File containing: Install class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 19. June 2013
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
         * @since 19. June 2013
         *
         */
        public function __construct($root)
        {
            $this->root = $root;
            
        }// __construct
        
        
        /**
         * Execute uninstall script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            // Notify
            info("Inspecting installation in [$this->root]....", SUCCESS, NONE);
            
            // Not found?
            if(!file_exists(realpath($this->root))) {
                return FAILED."(".NOT_FOUND.")";
            }// if
            info("DONE", SUCCESS);
            
            // Uninstall application
            info("Uninstalling [$this->root]....", SUCCESS, NONE);
            if(!rrmdir(realpath($this->root))) {
                return FAILED."(".RM_DIR_FAILED.")";
            }// if 
            info("DONE", SUCCESS);
           
            // Finished
            return true;
            
        }// execute


    }// Uninstall
