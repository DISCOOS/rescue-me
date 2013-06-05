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
     * Install class
     * 
     * @package 
     */
    class Install
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
         * Installation ini values
         * @var string
         */
        private $ini;
        
        
        /**
         * Constructor
         *
         * @param string $src RescueMe source archive (zip, filename)
         * @param string $root Installation root
         * @param array $ini Installation ini parameters
         * 
         * 
         * @since 19. June 2013, v. 7.60
         *
         */
        public function __construct($src, $root, $ini)
        {
            $this->src = $src;
            $this->root = $root;
            $this->ini = $ini;
            
        }// __construct
        
        
        /**
         * Execute install script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            // Notify
            info("Extracting [$this->src] to [$this->root]....", SUCCESS, NONE);
            
            // Do not overwrite existing
            if(file_exists($this->root) === TRUE) {
                return FAILED."(".DIR_EXISTS.")";
            }// if
            
            // Escape Phar context (HACK...)
            $content = file_get_contents($this->src);
            file_put_contents($this->src, $content);
            
            // Unpack source filoes to root
            $zip = new \ZipArchive();
            if (($error = $zip->open($this->src)) !== TRUE) {
                return FAILED."(".ZIP_OPEN_FAILED.":$error)";;
            }// if 
            
            // Extract source to root directory
            $zip->extractTo($this->root);
            $zip->close();
            
            // Cleanup
            unlink($this->src);
            
            // Get configuration template
            $config = file_get_contents("config.php");
            $config = replace_define_array($config, array
            (
                'SALT'              => $this->ini['SALT'], 
                'VERSION'           => $this->ini['VERSION'], 
                'TITLE'             => $this->ini['TITLE'], 
                'SMS_FROM'          => $this->ini['SMS_FROM'], 
                'DB_HOST'           => $this->ini['DB_HOST'], 
                'DB_NAME'           => $this->ini['DB_NAME'], 
                'DB_USERNAME'       => $this->ini['DB_USERNAME'], 
                'DB_PASSWORD'       => $this->ini['DB_PASSWORD'],
                'GOOGLE_API_KEY'    => $this->ini['GOOGLE_API_KEY'],
            ));
            
            // Create file
            if(file_put_contents($this->root."config.php", $config) === FALSE) {
                return FAILED."(".CONFIG_NOT_CREATED.")";;
            }// if
            
            // Finished
            return true;
            
            
        }// execute
        

    }// Install
