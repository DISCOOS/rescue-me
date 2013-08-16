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
         * @since 19. June 2013
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
                return DIR_EXISTS;
            }// if
            
            // Escape Phar context (HACK...)
            $content = file_get_contents($this->src);
            file_put_contents($this->src, $content);
            
            // Unpack source filoes to root
            $zip = new \ZipArchive();
            if (($error = $zip->open($this->src)) !== TRUE) {
                return ZIP_OPEN_FAILED.":$error";
            }// if 
            
            // Extract source to root directory
            $zip->extractTo($this->root);
            $zip->close();
            
            info("DONE", SUCCESS);
            
            // Get configuration template
            $config = file_get_contents("config.php");
            $config = replace_define_array($config, array
            (
                'SALT'              => $this->ini['SALT'], 
                'TITLE'             => $this->ini['TITLE'], 
                'SMS_FROM'          => $this->ini['SMS_FROM'], 
                'DB_HOST'           => $this->ini['DB_HOST'], 
                'DB_NAME'           => $this->ini['DB_NAME'], 
                'DB_USERNAME'       => $this->ini['DB_USERNAME'], 
                'DB_PASSWORD'       => $this->ini['DB_PASSWORD'],
                'DEFAULT_COUNTRY'    => $this->ini['DEFAULT_COUNTRY'],
                'GOOGLE_API_KEY'    => $this->ini['GOOGLE_API_KEY']
            ));
            
            // Create config.php
            if(file_put_contents($this->root."config.php", $config) === FALSE) {
                return CONFIG_NOT_CREATED;
            }// if
            
            // Create apache logs folder
            if(!is_dir($this->root."logs")) {
                mkdir($this->root."logs");
            }
            
            // Get common functions
            require $this->root."inc/common.inc.php";
            
            // Bootstrap RescueMe classes
            require $this->root."vendor/autoload.php";
            
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
            
            info("Creating database [$name]....", SUCCESS, NONE);
            if(DB::create($name) === FALSE) {
                return sprintf(DB_NOT_CREATED,"$name")." (check database credentials)";
            }// if
            info("DONE", SUCCESS);
            
            info("Importing [rescueme.sql]....", SUCCESS, NONE);
            if(($executed = DB::import($this->root."rescueme.sql")) === FALSE) {
                return sprintf(DB_NOT_IMPORTED,"rescueme.sql")." (".DB::error().")";
            }// if
            info("DONE", SUCCESS);
            
            if(User::isEmpty())
            {
                info("Initializing database....", SUCCESS);
                
                $fullname = in("Admin Full Name");
                $username = in("Admin Username (e-mail)");
                $password = in("Admin Password");
                $country = in("Admin Phone Country Code (ISO2)", Locale::getCurrentCountryCode());
                $mobile = in("Admin Phone Number Without Int'l Dial Code");
                
                if(!defined('SALT'))
                {
                    define('SALT', get($this->ini, 'SALT', null, false));
                }
                if(User::create($fullname, $username, $password, $country, $mobile) === FALSE) {
                    return ADMIN_NOT_CREATED." (".DB::error().")";
                }// if                
                
                info("Initializing database....DONE", SUCCESS);
                
            }
            
            info("Initializing modules....", SUCCESS);
            if(Module::install() !== false) {
                info("  System modules installed", SUCCESS);
            }
            foreach(User::getAll() as $user) {
                if($user->prepare()) {
                    info("  Modules for [$user->name] installed", SUCCESS);
                }
            }
            info("Initializing modules....DONE", SUCCESS);
            
            // Create VERSION file
            if(file_put_contents($this->root."VERSION", $this->ini['VERSION']) === FALSE) {
                return sprintf(VERSION_NOT_SET,$this->ini['VERSION']);
            }// if            
            
            // Cleanup
            unlink($this->src);
            
            // Finished
            return true;
            
            
        }// execute
        

    }// Install
