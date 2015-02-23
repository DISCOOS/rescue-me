<?php

    $verfile = dirname(__FILE__).DIRECTORY_SEPARATOR."VERSION";
    
    if(!file_exists($verfile)) {
        
        require "setup.php";
        
        die();
        
    }
    
    // TODO: Add to install/configure
    define('DEBUG', true); 
    
    // Allow usage on command line
    if(php_sapi_name() !== 'cli') session_start();

    // Silex routing instead of router.php
    // Still in early development!
    define('USE_SILEX', false);
    
    // RescueMe custom constants
    define('TITLE','RescueMe');
    
    // RescueMe constants
    define('VERSION', file_get_contents($verfile));
    
    // RescueMe timezone
    define('DEFAULT_TIMEZONE', 'UTC');
    
    // RescueMe locale
    define('COUNTRY_PREFIX', 'US');
    define('DEFAULT_LOCALE', 'en_US');
    
    // RescueMe application paths
    define('APP_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
    define('APP_PATH_INC', APP_PATH.'inc'.DIRECTORY_SEPARATOR);
    define('APP_PATH_CLASS', APP_PATH.'classes'.DIRECTORY_SEPARATOR);
    define('APP_PATH_LOCALE', APP_PATH.'locale'.DIRECTORY_SEPARATOR);
    define('APP_PATH_NEWS', APP_PATH.'news'.DIRECTORY_SEPARATOR);
    define('APP_PATH_HELP', APP_PATH.'help'.DIRECTORY_SEPARATOR);

    // Import class loaders
    require('vendor/autoload.php');
    
    // Include boostrap resources
    require(APP_PATH_INC.'rescueme.inc.php');
    
    // RescueMe application URI
    define('APP_URI', get_rescueme_uri());
    
    // RescueMe application URL
    define('APP_URL', get_rescueme_url());

    // RescueMe administration URI
    define('ADMIN_URI', APP_URI.'admin/');
    
    // RescueMe derived URLs
    define('LOCATE_URL', APP_URL.'l/#missing_id');
    define('ADMIN_TRACE_URL', APP_URL.'admin/missing/#missing_id');
        
    // Include dependent resources
    foreach(array('locale', 'common', 'gui') as $lib) {
        require(APP_PATH_INC.$lib.'.inc.php');
    }
    
    // RescueMe salt value
    define('SALT', '');

    // SMS integration constants
    define('SMS_FROM', 'RescueMe');

    // RescueMe database constants
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'rescueme');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    
    // Set current timezone
    if(\RescueMe\TimeZone::set(DEFAULT_TIMEZONE) === FALSE) {
        trigger_error("Failed to set timesone to [" . TIMEZONE . "]");
    }
    
    // Control debugging
    use_soap_error_handler(DEBUG);
    error_reporting(DEBUG ? ~0 : 0);
    ini_set('display_errors', DEBUG ? 1 : 0);
    
?>