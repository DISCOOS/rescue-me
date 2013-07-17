<?php    
    
    /**
	 * RescueMe Build Script
	 * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
    
    // Import common functions
    require 'src/config.php';
    require 'build/inc/common.inc.php';
    
    // Only run this when executed on the CLI
    if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        
        // Define constants
        define('HELP',"help");
        define('NAME',"name");
        define('ACTION',"action");
        define('UPDATE',"update");
        define('PREPARE',"prepare");
        define('PACKAGE',"package");
        
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
            case UPDATE:
                
                // Notify
                begin("rescueme update");
                
                // Execute action
                $status = update($opts);

                break;
                
            case PREPARE:
                
                // Notify
                begin("rescueme prepare");
                
                // Execute action
                $status = prepare($opts);

                break;
                
            case PACKAGE:
                
                // Notify
                begin("rescueme package");
                
                // Verify options
                $msg = (isset($opts['v']) ? null : "VERSION is missing");

                // Print help now?
                if(!empty($msg)) print_help(PACKAGE, $msg);
 
                // Execute action
                $status = package($opts);

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

        fatal("Run 'rescueme' on php-cli only!");
        
    }// else

    
    function prepare($opts)
    {
        // Notify
        info("Exporting [".DB_NAME."] to [src/rescueme.sql]....", SUCCESS, NONE);
        
        if(RescueMe\DB::export("src/rescueme.sql") === false)
        {
            return error(SQL_NOT_EXPORTED." (".DB::error().")");
        }
        return info("DONE", SUCCESS);
    }


    function update($opts)
    {
        // Notify
        info("Importing [src/rescueme.sql] into [".DB_NAME."]....", SUCCESS, NONE);
        
        if(RescueMe\DB::import("src/rescueme.sql") === false)
        {
            return error(SQL_NOT_IMPORTED." (".DB::error().")");
        }
        return info("DONE", SUCCESS);
    }


    function package($opts)
    {
        // Prepare sources before packaging
        if(($status = prepare($opts)) !== SUCCESS) {
            return $status;
        }
        
        // Get absolute path to rescueme package
        $package = "rescueme-" . $opts['v'] . ".phar";

        // Get package file
        $package = "dist/rescueme-" . $opts['v'];

        // Create folder if not exists
        if(!file_exists("dist"))
            mkdir("dist");

        // Delete if already exists
        if(file_exists($package))
            unlink("$package.phar");

        // Could be done in php.ini
        ini_set("phar.readonly", "0");

        // Creating new Phar
        $oPhar = new Phar("$package.phar");

        // Start buffering
        $oPhar->startBuffering();

        // Pointing main file which bootstrap all resources
        $oPhar->setDefaultStub('cli.php', 'cli.php');

        // Add build scripts source
        $oPhar->buildFromDirectory('build');

        // Add 5.4+ compatible class loader
        $oPhar->addFile("src/vendor/composer/ClassLoader.php", "classes/ClassLoader.php");

        // Prepare ini values
        $ini = "VERSION = " . $opts['v'];

        // Add ini file
        $oPhar->addFromString("rescueme.ini", $ini);

        // Prepare default config file
        $config = file_get_contents("src/config.tpl.php");
        $config = ini_define($config, array
            (
            'SALT', 'GOOGLE_API_KEY',
            'VERSION', 'TITLE', 'SMS_FROM',
            'DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD'
        ));

        // Add configuration template
        $oPhar->addFromString("config.php", $config);

        // Package source files as zip file
        $zip = new ZipArchive();
        $zip->open("src.zip", ZipArchive::CREATE);
        add_folder_to_zip("src/", $zip, "src/");
        $zip->deleteName("config.php");
        $zip->close();

        // Add source to package
        $oPhar->addFile("src.zip");

        // Write changes to file
        $oPhar->stopBuffering();

        // Cleanup
        unlink("src.zip");
        
        return SUCCESS;
    }// package


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
            case UPDATE:
                echo "RescueMe Update Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme compile' . PHP_EOL;
                break;
            case PREPARE:
                echo "RescueMe Prepare Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme prepare' . PHP_EOL;
                break;
            case PACKAGE:
                echo "RescueMe Package Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme package -v VERSION [OPTIONS]' . PHP_EOL;
                echo "PARAMETERS:" . PHP_EOL;
                echo "        -v,--version   Version" . PHP_EOL;
                echo "OPTIONS:" . PHP_EOL;
                echo "        -h,--help      Display this help" . PHP_EOL;
                break;
            case HELP:
            default:
                echo "RescueMe Build Script" . (isset($msg) ? " - " . $msg : "") . PHP_EOL;
                echo 'Usage: rescueme ACTION... [OPTIONS]... ' . PHP_EOL;
                echo "OPTIONS:" . PHP_EOL;
                echo "        -h,--help      Display this help" . PHP_EOL;
                echo "ACTION:" . PHP_EOL;
                echo "        Update         Update RescueMe (sql->db)" . PHP_EOL;
                echo "        prepare        Prepare RescueMe (db->sql)" . PHP_EOL;
                echo "        package        Package RescueMe (PHAR)" . PHP_EOL;
                echo "        help           Display help about an action" . PHP_EOL;
                break;
        }// switch
        
        // Finished
        echo PHP_EOL . PHP_EOL;

        exit($status);
        
    }// print_help
    
?>

