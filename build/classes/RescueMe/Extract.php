<?php

    /**
     * File containing: Extract class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 18. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    /**
     * Extract class
     * 
     * @package 
     */
    class Extract
    {
        /**
         * RescueMe source archive (zip, filename)
         * @var string
         */
        private $zip;
        
        
        /**
         * Extract to root
         * @var string
         */
        private $root;
        
        
        /**
         * Constructor
         * 
         * @param string $zip Path to zip achive
         * @param string $root Extract to root
         * 
         * @since 18. August 2013
         */
        public function __construct($zip, $root)
        {
            $this->zip = $zip;
            $this->root = $root;
            
        }// __construct
        
        
        /**
         * Execute unpack script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            
            // Notify
            info("  Extracting [$this->zip] to [$this->root]...");
            
            // Do not overwrite existing
            if(file_exists($this->root) === TRUE) {
                return error(DIR_EXISTS);
            }// if

            // Escape Phar context (HACK...)
            $content = file_get_contents($this->zip);
            file_put_contents($this->zip, $content);

            // Extract source files to root
            $zip = new \ZipArchive();
            if (($error = $zip->open($this->zip)) !== TRUE) {
                return error(ZIP_OPEN_FAILED.":$error");
            }// if 
            
            // Extract source to root directory
            $zip->extractTo($this->root);
            $zip->close();

            info("  Extracting [$this->zip] to [$this->root]...DONE");
                        
            // Finished
            return true;
            
            
        }// execute
        

    }// Install
