<?php

    /**
     * File containing: Install class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 19. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    use RescueMe\DB;
    use RescueMe\User;
    use RescueMe\Module;

    /**
     * Install class
     * 
     * @package 
     */
    class Install
    {
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
         * Use defaults if TRUE, prompt otherwise.
         * @var boolean
         */
        private $silent;
        
        /**
         * Update libraries
         * @var boolean
         */
        private $update;
        
        
        /**
         * Constructor
         *
         * @param string $root Installation root
         * @param array $ini Installation ini parameters
         * @param boolean $silent Use defaults if TRUE, prompt otherwise.
         * @param boolean $update Update libraries if installed.
         * 
         * 
         * @since 19. June 2013
         *
         */
        public function __construct($root, $ini, $silent, $update)
        {
            $this->root = $root;
            $this->ini = $ini;
            $this->silent = $silent;
            $this->update = $update;
            
        }// __construct
        
        
        /**
         * Execute install script
         * 
         * @return mixed TRUE if success, error message otherwise.
         * 
         */
        public function execute()
        {
            begin(in_phar() ? INSTALL : CONFIGURE);
            
            // In dev-mode?
            if(!in_phar()) {
                
                $this->initComposer();
                
                $this->initLibs();
                
            }
            
            // Bootstrap libraries
            require $this->root.DIRECTORY_SEPARATOR."vendor/autoload.php";            
            
            $this->initConfig();
            
            $this->initDB();
            
            $this->initModules();
            
            $this->initMinify();
            
            // Create VERSION file
            if(file_put_contents($this->root.DIRECTORY_SEPARATOR."VERSION", $this->ini['VERSION']) === FALSE) {
                return error(sprintf(VERSION_NOT_SET,$this->ini['VERSION']));
            }// if
            
            done(in_phar() ? INSTALL : CONFIGURE);
            
            // Finished
            return true;
            
            
        }// execute
        
        
        private function initConfig() {
            
            // Get template from phar?
            if(in_phar()) {
                $config = file_get_contents("config.tpl.php");
                $config_minify = file_get_contents("config.minify.tpl.php");
            } 
            // Get template from source?
            else {
                $config = file_get_contents($this->root.DIRECTORY_SEPARATOR."config.tpl.php");
                $config_minify = file_get_contents($this->root.DIRECTORY_SEPARATOR."config.minify.tpl.php");
            }            
            
            // Get config template
            $config = replace_define_array($config, array
            (
                'SALT'              => $this->ini['SALT'], 
                'TITLE'             => $this->ini['TITLE'], 
                'SMS_FROM'          => $this->ini['SMS_FROM'], 
                'DB_HOST'           => $this->ini['DB_HOST'], 
                'DB_NAME'           => $this->ini['DB_NAME'], 
                'DB_USERNAME'       => $this->ini['DB_USERNAME'], 
                'DB_PASSWORD'       => $this->ini['DB_PASSWORD'],
                'DEFAULT_COUNTRY'   => $this->ini['DEFAULT_COUNTRY'],
                'TIMEZONE'          => $this->ini['TIMEZONE'],
                'GOOGLE_API_KEY'    => $this->ini['GOOGLE_API_KEY']
            ));
            
            // Create config.php
            if(file_put_contents($this->root.DIRECTORY_SEPARATOR."config.php", $config) === FALSE) {
                return error(CONFIG_NOT_CREATED);
            }// if
            
            // Get config minify template
            $config_minify = replace_define_array($config_minify, array
            (
                'MINIFY_MAXAGE'      => $this->ini['MINIFY_MAXAGE']
            ));
            
            // Create config.php
            if(file_put_contents($this->root.DIRECTORY_SEPARATOR."config.minify.php", $config_minify) === FALSE) {
                return error(CONFIG_MINIFY_NOT_CREATED);
            }// if
            
            // Create apache logs folder
            if(!file_exists(realpath($this->root.DIRECTORY_SEPARATOR."logs"))) {
                mkdir($this->root.DIRECTORY_SEPARATOR."logs");
            }            
        }
        
        private function initComposer() {
            
            $inline = true;
            info("  Installing composer....", INFO, NONE);
            
            $composer = $this->root.DIRECTORY_SEPARATOR."composer.phar";
            if(!file_exists($composer)) {
                
                // TODO: Move installer to github
                $installer = 'http://rescueme.discoos.org/installer';
                
                $cmd = 'php -r "eval(\'?>\'.file_get_contents(\''.$installer.'\'));"';
                exec($cmd, $output, $retval);
                if($retval !== 0) {
                    $output = implode("\n", $output);
                    return error("Failed to download composer: \n$output\n");
                }                                       
            }
            info($inline ? "SKIPPED" : "  Installing composer...DONE");
        }
        
        
        private function initLibs() {
            
            info("  Initializing libraries...");
            
            $vendor = $this->root.DIRECTORY_SEPARATOR."vendor";
            $exists = file_exists($vendor);
            if($exists) {
                
                info("     Updating libraries...", INFO, NONE);

                if($this->update) {
                
                    $composer = $this->root.DIRECTORY_SEPARATOR."composer.phar";

                    $cmd = "php $composer update --no-scripts --working-dir='$this->root'";
                    exec($cmd, $output, $retval);
                    if($retval !== 0) {
                        $output = implode("\n", $output);
                        return error("Failed to update libraries: \n$output\n");
                    }                                       

                    info("DONE");                
                    
                } else {
                    
                    info("SKIPPED");                
                }
                
            } elseif(!$exists) {
                
                info("     Installing libraries...", INFO, NONE);
                
                $composer = $this->root.DIRECTORY_SEPARATOR."composer.phar";
                
                $cmd = "php $composer install --no-scripts --working-dir='$this->root'";
                exec($cmd, $output, $retval);
                if($retval !== 0) {
                    $output = implode("\n", $output);
                    return error("Failed to install libraries: \n$output\n");
                }                                       

                info("DONE");
            }
            
            info("  Initializing libraries....DONE");
        }
        
        
        private function initDB() {

            // Install database
            $name = get($this->ini, 'DB_NAME', null, false);
            if(!defined('DB_NAME'))
            {
                // RescueMe database constants
                define('DB_NAME', $name);
                define('DB_HOST', get($this->ini, 'DB_HOST', null, false));
                define('DB_USERNAME', get($this->ini, 'DB_USERNAME', null, false));
                define('DB_PASSWORD', get($this->ini, 'DB_PASSWORD', null, false));
            }
            
            info("  Creating database [$name]....", INFO, NONE);
            if(DB::create($name) === FALSE) {
                return error(sprintf(DB_NOT_CREATED,"$name")." (check database credentials)");
            }// if
            info("DONE");
            
            info("  Importing [rescueme.sql]....", INFO, NONE);
            if(($executed = DB::import($this->root.DIRECTORY_SEPARATOR."rescueme.sql")) === FALSE) {
                return error(sprintf(DB_NOT_IMPORTED,"rescueme.sql")." (".DB::error().")");
            }// if
            info("DONE");            
           
            if(User::isEmpty())
            {
                info("  Initializing database....", INFO);

                $fullname = in("  Admin Full Name");
                $username = in("  Admin Username (e-mail)");
                $password = in("  Admin Password");
                $country = in("  Admin Phone Country Code (ISO2)", Locale::getCurrentCountryCode());
                $mobile = in("  Admin Phone Number Without Int'l Dial Code");

                if(!defined('SALT'))
                {
                    define('SALT', get($this->ini, 'SALT', null, false));
                }
                if(User::create($fullname, $username, $password, $country, $mobile) === FALSE) {
                    return error(ADMIN_NOT_CREATED." (".DB::error().")");
                }// if                

                info("  Initializing database....DONE", INFO);            
            }
        }
        
        private function initModules() {
            
            $inline = true;
            info("  Initializing modules....", INFO, NONE);
            
            if(Module::install() !== false) {
                info("    System modules installed", INFO, BOTH);
                $inline = false;
            }
            
            $users = User::getAll();
            if($users !== false) {
                foreach($users as $user) {
                    if($user->prepare()) {
                        info("    Modules for [$user->name] installed", INFO, $inline ? BOTH : POST);
                        $inline = false;
                    }
                }
            }
            info($inline ? "SKIPPED" : "  Initializing modules....DONE", INFO);
            
        }
        
        private function initMinify() {
            
            $inline = true;
            info("  Initializing minify...", INFO, NONE);
            
            $cache = $this->root.DIRECTORY_SEPARATOR."min".DIRECTORY_SEPARATOR."cache";           
            if(!file_exists($cache)) {
                if(!mkdir($cache)) {
                    return error(sprintf(DIR_NOT_CREATED,$cache));
                }
                $cache = realpath($this->root.DIRECTORY_SEPARATOR."min".DIRECTORY_SEPARATOR."cache");                
                if(is_linux()) {
                    if(!is_sudo()) {
                        rmdir($cache);
                        return error(NOT_SUDO);
                    }
                    $user = "www-data:www-data";
                    if(!$this->silent) {
                        $user = in("    Webservice username", $user, PRE);
                        $inline = false;
                    }
                    $user = explode(":", $user);
                    if(!chown($cache, $user[0])) {
                        rmdir($cache);
                        return error(sprintf(CHOWN_NOT_SET,$user[0],$cache));
                    }                    
                    if(isset($user[1]) && !chgrp($cache, $user[1])) {
                        rmdir($cache);
                        return error(sprintf(CHGRP_NOT_SET,$user[1],$cache));
                    }                    
                }
            }
            info($inline ? "SKIPPED" : "  Initializing minify....DONE", INFO);            
            
        }



    }// Install
