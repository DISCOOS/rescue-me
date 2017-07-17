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
         * RescueMe import parameters
         * @var array
         */
        private $params;


        /**
         * Sql file
         * @var string
         */
        private $file;


        /**
         * Constructor
         *
         * @param string $file Sql file
         * @param array $params Import parameters
         *
         * @since 18. August 2013
         */
        public function __construct($file, $params) {
            $this->params = $params;
            $this->file = $file;

        }// __construct


        /**
         * Execute database import script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            // Get database name and import file
            $name = get($this->params, PARAM_DB, null);
            $version = get($this->params, PARAM_VERSION, null);

            info("  Importing database [$name:$version] from [$this->file]...");

            // Verify that import is allowed with given parameters
            if(TRUE !== ($message = $this->verify($name, $version, $this->file))) {
                return error(sprintf('    %s. %s', sprintf(DB_NOT_IMPORTED, 'Database'), $message));
            }

            // Get queries
            $queries = DB::instance()->fetch_queries(file($this->file));

            if (DB::instance()->exists($name)) {
                info(sprintf('    Database [%s] exists', $name));
                info("  Importing database [$name:$version] from [$this->file]...SKIPPED");
            }
            else {
                if(!DB::instance()->create($name)) {
                    $message = 'check database credentials';
                    return error(sprintf('    %s - %s', sprintf(DB_NOT_IMPORTED, 'Database'), $message));
                }
                info("    Database schema [$name] created");

                try {
                    $count = DB::instance()->source($queries);
                    info(sprintf('    Sourced %s queries into [%s]', $count, $name));
                } catch (DBException $e) {
                    return error(SQL_NOT_IMPORTED . ' ' . $e);
                }
                DB::instance()->setVersion($version);

                info("  Importing database [$name:$version] from [$this->file]...DONE");
            }

            return true;

        }// import

        private function verify($name, $version, $file)
        {
            // Connect to database
            DB::instance()->connect(
                $this->params[PARAM_HOST],
                $this->params[PARAM_USERNAME],
                $this->params[PARAM_PASSWORD],
                $name);

            if(is_null($version) || empty($version)) {
                return sprintf('Version is missing', $file);
            }
            else if(!file_exists($file)) {
                return sprintf('File %s not found', $file);
            }
            else if(!endsWith($file,'.sql')) {
                return sprintf('File %s does not end with sql', $file);
            }
            return true;
        }

    }// Import
