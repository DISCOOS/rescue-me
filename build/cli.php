<?php

    /**
	 * RescueMe Package command line interface
	 * 
	 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}  
	 *
     * @since 19. June 2013
	 * 
	 * @author Kenneth GulbrandsÃ¸y <kenneth@onevoice.no>
	 */

    // Import common functions
    require 'inc/common.inc.php';

    // Only run this when executed on the CLI
    if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        
        // Import class loader class
        require('classes/SplClassLoader.php');

        // Create class loader instance
        $autoloader = new SplClassLoader('RescueMe','classes');

        // Register class loader instance with SPL
        $autoloader->register();

        // Define constants
        define('HELP',"help");
        define('NAME',"name");
        define('ACTION',"action");
        define('VERSION',"version");
        define('INSTALL',"install");
        define('INSTALL_DIR',"install-dir");
        
        // Get options
        $opts = parse_opts($argv, array('h'));

        // Get action
        $action = $opts[ACTION];
        
        // Get error message?
        $msg = (empty($action) ? "Show help: -h | help ACTION" : null);        
        
        // Print help now?
        if(isset($opts['h'])) print_help();

        // Print help with error message?
        if(isset($msg))  print_help(HELP, "Show help: -h");
        
        // Assume success
        $status = SUCCESS;
        
        // Perform action
        switch($action)
        {
            case INSTALL:
                
                // Notify
                begin("RescueMe $action");

                // Get default path install path
                $root = get($opts, INSTALL_DIR, getcwd(), false);
                
                // Get default ini values
                $ini = parse_ini_file("rescueme.ini");
                
                // Escape version
                $ini['VERSION'] = str_escape($ini['VERSION']);
                
                // Use current working directory?
                if($root === ".") $root = getcwd();
                
                // Ensure trailing slash
                $root = rtrim($root,"/")."/";
                
                // Get current?
                if(file_exists(realpath($root."config.php"))) {
                    
                    // Get current configuration
                    $config = file_get_contents($root."config.php");
                    $config = get_define_array($config, array
                    (
                        'SALT', 'VERSION', 'TITLE', 'SMS_FROM', 
                        'DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD',
                        'GOOGLE_API_KEY'
                    ));
                    
                    // Merge current config values with default ini values
                    $ini = array_merge($ini, $config);
                    
                }// 
                    
                // TODO: Get parameters from user (when !silent)
                $ini['SALT']             = str_escape(in("Salt", get($ini, "SALT", str_rnd()), PRE));
                $ini['TITLE']            = str_escape(in("Title", get($ini, "TITLE", "RescueMe")));
                $ini['SMS_FROM']         = str_escape(in("Sender", get($ini, "SMS_FROM", "RescueMe")));
                $ini['DB_HOST']          = str_escape(in("DB Host", get($ini, "DB_HOST", "localhost")));
                $ini['DB_NAME']          = str_escape(in("DB Name", get($ini, "DB_NAME", "rescueme")));
                $ini['DB_USERNAME']      = str_escape(in("DB Username", get($ini, "DB_USERNAME", "root")));
                $ini['DB_PASSWORD']      = str_escape(in("DB Password", get($ini, "DB_PASSWORD", "''")));
                $ini['GOOGLE_API_KEY']   = str_escape(in("Google API key", get($ini, "GOOGLE_API_KEY", "''"), NONE, false));
                
                // Uninstall current?
                if(file_exists(realpath($root)))
                {
                    $uninstall = new RescueMe\Uninstall($root);
                    
                    // Unistall successfull?
                    if($uninstall->execute() !== true) {
                        $status = error($config, ERROR, BOTH); 
                        break;
                    }// if
                    
                }
                
                // Create install
                $install = new RescueMe\Install("src.zip", $root, $ini);

                // Execute installation
                if(($message = $install->execute()) !== true) {
                    $status = error($message, ERROR, BOTH); break;
                }// if
                
                break;

            case HELP:

                // Get action
                $help = $opts[NAME];

                // Verify help action
                $msg = (isset($help) ? null : "ACTION is missing");

                // Print help now
                print_help($help, $msg);
                
                break;

            default:

                print_help();
                
        }// switch
        
        // Finised
        done("RescueMe $action", $status);
        
    }// if	 
    
    else {

        fatal("Run 'cli' on php-cli only!");
        
    }// else
    

    /**
     * Print help. 
     * 
     * Peforms a forced exit.
     * 
     * @param string $action Action
     * @param string $msg Message
     * @param int $status Exit status
     */
    function print_help($action = HELP, $msg = null, $status = ERROR)
    {
        switch($action)
        {
            case INSTALL:
                echo "RescueMe install Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme package [OPTIONS]... ' . PHP_EOL;
                echo "OPTIONS:" . PHP_EOL;
                echo "        -o,--install-dir  Install directory [default: current]" . PHP_EOL;
                echo "        -h,--help         Display this help" . PHP_EOL;
                break;
            case HELP:
            default:
                echo "RescueMe Package Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme [OPTIONS]... ACTION... ' . PHP_EOL;
                echo "OPTIONS:" . PHP_EOL;
                echo "        -h,--help      Display this help" . PHP_EOL;
                echo "ACTION:" . PHP_EOL;
                echo "        install        Install RescueMe" . PHP_EOL;
                echo "        help           Display help about an action" . PHP_EOL;
                break;
        }// switch
        
        // Finished
        echo PHP_EOL . PHP_EOL;

        exit($status);
        
    }// print_help
    
?>
    