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
            out("Inspecting installation in [$this->root]....", PRE, COLOR_INFO);
            
            // Not found?
            if(!file_exists(realpath($this->root))) {
                return FAILED."(".NOT_FOUND.")";
            }// if
            
            // Uninstall application
            out("Uninstalling [$this->root]....", PRE, COLOR_INFO);
            if(!rrmdir(realpath($this->root))) {
                return FAILED."(".RM_DIR_FAILED.")";
            }// if 
           
            // Finished
            return true;
            
        }// execute


    }// Uninstall
