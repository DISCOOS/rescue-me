<?php

    /**
	 * RescueMe Package command line interface
	 * 
	 * @copyright Copyright 2013 {@link http://www.onevoice.no One Voice AS} 
	 *
     * @since 19. June 2013, v. 7.60
	 * 
	 * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
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
        if(isset($opts['h']))  print_help();

        // Print help with error message?
        if(isset($msg))  print_help(HELP, "Show help: -h");
        
        // Assume success
        $status = SUCCESS;
        
        // Perform action
        switch($action)
        {
            case INSTALL:
                
                // Notify
                begin("rescueme $action");

                // Get default path install path
                $root = get($opts, INSTALL_DIR, getcwd());
                
                // Get default ini values
                $ini = parse_ini_file("rescueme.ini");
                
                // Use current working directory?
                if($root === ".") $root = getcwd();
                
                // Ensure trailing slash
                $root = rtrim($root,"/")."/";
                
                // Uninstall current?
                if(file_exists(realpath($root))) {
                    
                    $uninstall = new RescueMe\Uninstall($root);
                    
                    // Unistall successfull?
                    if(!is_array($config = $uninstall->execute())) {
                        $status = error($config, ERROR, NONE); break;
                    }// if
                    
                    // Merge current config values with default ini values
                    $ini = array_merge($ini, $config);
                    
                }// 
                    
                // TODO: Get parameters from user (when !silent)
                $ini['SALT']             = in("Salt", get($ini, "SALT", str_rnd(), true), PRE);
                $ini['TITLE']            = in("Title", get($ini, "TITLE", "RescueMe", true));
                $ini['SMS_FROM']         = in("Sender", get($ini, "SMS_FROM", "RescueMe", true));
                $ini['DB_HOST']          = in("DB Host", get($ini, "DB_HOST", "localhost", true));
                $ini['DB_NAME']          = in("DB Name", get($ini, "DB_NAME", "rescueme", true));
                $ini['DB_USERNAME']      = in("DB Username", get($ini, "DB_USERNAME", "root", true));
                $ini['DB_PASSWORD']      = in("DB Password", get($ini, "DB_PASSWORD", "''", true));
                $ini['GOOGLE_API_KEY']   = in("Google API key", get($ini, "GOOGLE_API_KEY", "''", true));
                
                // Create install
                $install = new RescueMe\Install("src.zip", $root, $ini);

                // Execute installation
                if(($message = $install->execute()) !== true) {
                    $status = error($message, ERROR, NONE); break;
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
        done("rescueme $action", $status);
        
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
            case HELP:
            default:
                echo "RescueMe Package Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme [OPTIONS]... ACTION... ' . PHP_EOL;
                echo "OPTIONS:" . PHP_EOL;
                echo "        -h,--help      Display this help" . PHP_EOL;
                echo "ACTION:" . PHP_EOL;
                echo "        install        Install RescueMe" . PHP_EOL;
                echo "        help           Display help about an action" . PHP_EOL;
        }// switch
        
        // Finished
        echo PHP_EOL . PHP_EOL;

        exit($status);
        
    }// print_help
    
?>
    