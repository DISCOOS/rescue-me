<?php

    /**
     * File containing: Import class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 18. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    /**
     * Import class
     * 
     * @package 
     */
    class Import
    {
        /**
         * RescueMe database host
         * @var string
         */
        private $host;
        
        
        /**
         * RescueMe database username
         * @var string
         */
        private $username;
        
        
        /**
         * RescueMe database password
         * @var string
         */
        private $password;
        
        
        /**
         * RescueMe database name
         * @var string
         */
        private $db;
        
        
        /**
         * Installation root
         * @var string
         */
        private $root;
        
        
        /**
         * Constructor
         * 
         * @param string $host RescueMe database host
         * @param string $username RescueMe database username
         * @param string $password RescueMe database password
         * @param string $db RescueMe database name
         * @param string $root Installation root
         * 
         * @since 18. August 2013
         */
        public function __construct($host, $username, $password, $db, $root)
        {
            $this->host = $host;
            $this->username = $username;
            $this->password = $password;
            $this->db = $db;
            $this->root = $root;
            
        }// __construct
        
        
        /**
         * Execute import script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            begin(IMPORT);

            $path = "$this->root".DIRECTORY_SEPARATOR."db".DIRECTORY_SEPARATOR."init.sql";

            // Notify
            info("  Importing [$path] into [".$this->db."]...",
                BUILD_INFO, NEWLINE_NONE);
            
            // Connect to database
            DB::instance()->connect($this->host, $this->username, $this->password, $this->db);
            
            // Attempt to import
            if(DB::import($path) === false)
            {
                return error(SQL_NOT_IMPORTED." (".DB::error().")");
            }
            
            info("DONE");
            
            done(IMPORT);
            
            // Finished
            return true;
            
            
        }// execute
        

    }// Import
