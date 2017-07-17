<?php

    /**
     * File containing: Export class
     * 
     * @copyright Copyright 2017 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 17. July 2017
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    /**
     * Export class
     * 
     * @package 
     */
    class Baseline
    {
        /**
         * RescueMe Installation parameters
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
         *
         * @param array $params Installation parameters
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
            $version = get($this->params, PARAM_VERSION, null);
            $path = $this->root.DIRECTORY_SEPARATOR."init.sql";
            $baseline = $this->root.DIRECTORY_SEPARATOR."baselines".DIRECTORY_SEPARATOR."v$version.sql";

            // Notify
            info("  Baseline [$name:$version] from [$path]...");

            // Verify that init sql is valid
            if(TRUE !== ($message = $this->verify($name, $version, $baseline))) {
                return error(sprintf('    Database %s. %s', sprintf(DB_NOT_BASELINED, $name), $message));
            }

            // Copy content into baseline file
            file_put_contents($baseline, file_get_contents($path));

            // Notify
            info("    [$baseline] generated");

            // Notify
            info("  Baseline [$name:$version] from [$path]...DONE");

            // Finished
            return true;

        }// export

        private function verify($name, $version, $baseline)
        {
            if (is_null($name) || empty($name)) {
                return 'Database name is missing';
            }
            else if(is_null($version) || empty($version)) {
                return 'Database version is missing';
            }
            else if(file_exists($baseline)) {
                return sprintf('Baseline %s exists', $baseline);
            }

            return true;
        }
        

    }// Export
