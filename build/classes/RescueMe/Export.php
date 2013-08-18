<?php

    /**
     * File containing: Export class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 18. August 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    /**
     * Export class
     * 
     * @package 
     */
    class Export
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
            begin(EXPORT);
            
            // Notify
            info("  Exporting [".$this->db."] into [$this->root/rescueme.sql]....", INFO, NONE);

            // Connect to database
            DB::instance()->connect($this->host, $this->username, $this->password, $this->db);
            
            // Attempt to export
            if(DB::export("$this->root/rescueme.sql") === false)
            {
                return error(SQL_NOT_EXPORTED." (".DB::error().")");
            }
            
            info("DONE");
            
            done(EXPORT);
            
            // Finished
            return true;
            
            
        }// execute
        

    }// Export
