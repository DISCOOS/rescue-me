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
     * Database Seed class
     *
     * @package
     */
    class Seed {

        /**
         * RescueMe seed parameters
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
         * @param array $params Seed parameters
         *
         * @since 11. July 2017
         */
        public function __construct($root, $params)
        {
            $this->params = $params;
            $this->root = $root;

        }// __construct


        /**
         * Execute database seed script
         *
         * @return bool|string TRUE if success, error message otherwise.
         *
         */
        public function execute()
        {
            $name = get($this->params, PARAM_DB, null);
            $version = get($this->params, PARAM_VERSION, null);

            info("  Seeding database [$name:$version]...");

            $skipped = true;

            // Verify that init sql is valid
            if(TRUE !== ($message = $this->verify($name, $version))) {
                return error(sprintf('    %s. %s', sprintf(DB_NOT_SEEDED, 'Database'), $message));
            }

            // Seed database with one admin user?
            if (User::isEmpty()) {

                info("    >> Add administrator <<");

                $fullname = in("    Full Name");
                $username = in("    Username (e-mail)");
                $password = in("    Password");
                $country = strtoupper(in("    Default Country Code (ISO2)",
                        trim($this->params["COUNTRY_PREFIX"],'\'"')));
                $mobile = in("    Phone Number Without Int'l Dial Code");

                $hash = User::hash($password, $this->params["SALT"]);
                $user = User::create($fullname, $username, $hash, $country, $mobile, Roles::ADMIN);
                if ($user === FALSE) {
                    return error(ADMIN_NOT_CREATED . " (" . DB::error() . ")");
                }

                $skipped = false;
            } else {
                info("    Administrator(s) exists");
            }

            // Seed database with default permissions
            if (($count = Roles::prepare(Roles::ADMIN)) > 0) {
                info("    Added $count administrator permissions...OK");
                $skipped = false;
            }
            if (($count = Roles::prepare(Roles::OPERATOR)) > 0) {
                info("    Added $count operator permissions...OK");
                $skipped = false;
            }
            if (($count = Roles::prepare(Roles::PERSONNEL)) > 0) {
                info("    Added $count personnel permissions...OK");
                $skipped = false;
            }

            info("  Seeding database [$name:$version]..." . ($skipped ? 'SKIPPED' : 'DONE'));

            // Finished
            return true;

        }// seed

        /**
         * @param $name
         * @param $version
         * @return bool|string
         */
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
            else if(DB::legacyVersion()) {
                return 'Legacy database, execute migrate instead';
            }
            else if($version !== ($latest = DB::latestVersion())) {
                return sprintf('Database mismatch, found [%s], expected [%s]', $latest, $version);
            }

            return true;
        }


    }// Seed