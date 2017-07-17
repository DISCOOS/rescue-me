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
         * RescueMe export parameters
         * @var array
         */
        private $params;


        /**
         * Database directory root
         * @var string
         */
        private $root;


        /**
         * Constructor
         *
         * @param string $root Database directory root
         * @param array $params Export parameters
         *
         * @since 18. August 2013
         */
        public function __construct($root, $params) {
            $this->params = $params;
            $this->root = $root;

        }// __construct
        
        
        /**
         * Execute export structure script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            // Get export path and database name
            $name = get($this->params, 'DB_NAME', null);
            $path = "$this->root".DIRECTORY_SEPARATOR."init.sql";

            // Notify
            info("  Exporting [".$name."] into [$path]...", BUILD_INFO, NEWLINE_NONE);

            // Connect to database
            DB::instance()->connect(
                $this->params[PARAM_HOST],
                $this->params[PARAM_USERNAME],
                $this->params[PARAM_PASSWORD],
                $name);

            // Attempt to export
            if(DB::export($path, "RescueMe Database Export Script") === false)
            {
                return error(SQL_NOT_EXPORTED." (".DB::error().")");
            }

            info("DONE");

            // Finished
            return true;

        }// export
        

    }// Export
