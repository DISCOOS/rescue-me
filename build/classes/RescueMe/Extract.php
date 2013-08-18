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
        private $src;
        
        
        /**
         * Installation root
         * @var string
         */
        private $root;
        
        
        /**
         * Constructor
         * 
         * @param string $src Source rooot
         * @param string $root Installation root
         * 
         * @since 18. August 2013
         */
        public function __construct($src, $root)
        {
            $this->src = $src;
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
            begin(EXTRACT);
            
            // Notify
            info("  Extracting [$this->src] to [$this->root]....", INFO, NONE);
            
            // Do not overwrite existing
            if(file_exists($this->root) === TRUE) {
                return error(DIR_EXISTS);
            }// if

            // Ensure source exists
            if(!file_exists($this->src) === TRUE) {
                return error(sprintf("%s ".NOT_FOUND, $this->src));
            }// if
            
            // Escape Phar context (HACK...)
            $content = file_get_contents($this->src);
            file_put_contents($this->src, $content);
            
            // Extract source filoes to root
            $zip = new \ZipArchive();
            if (($error = $zip->open($this->src)) !== TRUE) {
                return error(ZIP_OPEN_FAILED.":$error");
            }// if 
            
            // Extract source to root directory
            $zip->extractTo($this->root);
            $zip->close();
            
            info("DONE");
                        
            done(EXTRACT);
            
            // Finished
            return true;
            
            
        }// execute
        

    }// Install
