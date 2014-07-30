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
            begin(UNINSTALL);
            
            // Notify
            info("  Inspecting [$this->root]....", BUILD_INFO, NEWLINE_NONE);
            
            // Not found?
            if(!file_exists(realpath($this->root))) {
                return error(sprintf("[%s] not found",$this->root));
            }// if
            info("DONE");
            
            // Uninstall application
            info("  Uninstalling [$this->root]....", BUILD_INFO, NEWLINE_NONE);
            if(!rrmdir(realpath($this->root))) {
                return error(FAILED."(".RM_DIR_FAILED.")");
            }// if             
            info("DONE");
           
            done(UNINSTALL);
            
            // Finished
            return true;
            
        }// execute


    }// Uninstall
