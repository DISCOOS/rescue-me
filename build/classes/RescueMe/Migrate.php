<?php

    /**
     * File containing: Export class
     *
     * @copyright Copyright 2017 {@link http://www.discoos.org DISCO Open Source}
     *
     * @since 11. Juli 2017
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;

    /**
     * Database Migration class
     *
     * @package
     */
    class Migrate {

        /**
         * RescueMe migrate parameters
         * @var array
         */
        private $params;


        /**
         * Database directory root
         * @var string
         */
        private $src;

        /**
         * Database directory root
         * @var string
         */
        private $db;


        /**
         * Constructor
         *
         * @param string $src Source directory root
         * @param string $db Database directory root
         * @param array $params Migrate parameters
         *
         * @since 11. July 2017
         */
        public function __construct($src, $db, $params) {
            $this->params = $params;
            $this->src = $src;
            $this->db = $db;
        }// __construct


        /**
         * Execute migration script
         *
         * @return bool|string TRUE if success, error message otherwise.
         *
         */
        public function execute()
        {
            $name = get($this->params, PARAM_DB, null);
            $version = get($this->params, PARAM_VERSION, null);
            $verfile = implode(DIRECTORY_SEPARATOR, array($this->src,"VERSION"));

            info("  Migrating database [$name:$version]...");

            // Substitute supported variables
            $substitute = function($query) use($name) {
                return str_replace('${schema}', $name, $query);
            };

            // Verify that init sql is valid
            if(TRUE !== ($message = $this->verify($name, $version))) {
                return error(sprintf('    %s. %s', sprintf(DB_NOT_MIGRATED, 'Database'), $message));
            }

            // Only attempt to migrate if database is imported
            if($latest = $this->getLatestVersion($version)) {

                $latest = strtolower($latest);
                $migrations = $this->db.DIRECTORY_SEPARATOR."migrations";

                info(sprintf('    Latest version: %s', $latest));

                foreach ($this->getFiles($migrations) as $basename) {
                    $version = preg_split('/__/', $basename);
                    if(count($version) > 1) {
                        $number = ltrim(strtolower($version[0]),'v');
                        $description = preg_replace('/_/',' ',$version[1]);
                        info(sprintf('    Found: %s (%s)', $number, $description));
                        if($this->before($latest, $number)) {
                            $file = $migrations.DIRECTORY_SEPARATOR."$basename.sql";
                            info(sprintf('      %s < %s', $latest, $number));
                            info(sprintf('      Source file: ' . realpath($file)));
                            try {
                                $queries = DB::instance()->fetch_queries(file($file), $substitute);
                                $count = DB::instance()->source($queries);
                                info(sprintf('      Sourced %s queries into %s:%s', $count, $name, $number));
                                // Update database structure version
                                DB::insert("versions", array('version_name' => $number));
                                // Update application version
                                file_put_contents($verfile, $number);
                                $latest = $number;
                                info(sprintf('      Latest version: %s', $latest));
                            } catch (DBException $e) {
                                return error(sprintf('    %s. %s', sprintf(SQL_NOT_IMPORTED, 'Database'), $e));
                            }
                        }
                        else {
                            info(sprintf('      %s %s %s', $latest, $latest === $number ? '=' : '>', $number));
                        }
                    }
                }
            }

            info("  Migrating database...." . ($latest ? 'DONE' : 'SKIPPED'));

            // Finished
            return $latest !== FALSE;

        }// migrate

        private function verify($name, $version)
        {
            // Connect to database
            DB::instance()->connect(
                $this->params[PARAM_HOST],
                $this->params[PARAM_USERNAME],
                $this->params[PARAM_PASSWORD],
                $name);

            if (!DB::instance()->exists($name)) {
                return sprintf('Database [%s] does not exists', $name);
            }
            else if(!(DB::legacyVersion() || $version === DB::latestVersion())) {
                return sprintf('Database [%s] is up to date', $name);
            }

            return true;
        }

        /**
         * Determine latest version to compare with
         * @param $version
         * @return string
         */
        private function getLatestVersion($version) {
            if(($latest = DB::latestVersion()) === false) {
                $latest = $version;
            }
            return $latest;
        }

        /**
         * Get basenames of alle sql files in given directory
         * @param $path
         * @return array
         */
        private function getFiles($path) {
            $basenames = array();
            foreach(new \DirectoryIterator($path) as $fileInfo) {
                if($fileInfo->isDot() || "sql" !== strtolower($fileInfo->getExtension())) continue;
                $basenames[] = strtolower($fileInfo->getBasename('.sql'));
            }
            asort($basenames);
            return $basenames;
        }

        /**
         * Check if latest version is lower than next version.
         * @param $latest
         * @param $next
         * @return bool
         */
        private function before($latest, $next) {
            $first = reset(preg_split('/\./', $latest));
            return is_numeric($first) && version_compare($latest, $next)<0;
        }

    }// Migrate