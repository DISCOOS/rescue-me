<?php
    session_start();
    
    // RescueMe application paths
    define('APP_PATH', dirname(__FILE__).'/');
    define('APP_PATH_INC', APP_PATH.'inc/');
    define('APP_PATH_CLASS', APP_PATH.'class/');

    // RescueMe administration paths
    define('ADMIN_PATH', APP_PATH.'admin/');
    define('ADMIN_PATH_INC', ADMIN_PATH.'inc/');
    define('ADMIN_PATH_GUI', ADMIN_PATH.'gui/');
    define('ADMIN_PATH_CLASS', ADMIN_PATH.'class/');
    
    // Load common resources
    require('inc/common.inc.php');
    
    // RescueMe application URI
    define('APP_URI', get_rescueme_uri());
    
    // RescueMe application URL
    define('APP_URL', get_rescueme_url());

    // RescueMe administration URI
    define('ADMIN_URI', APP_URI.'admin/');
    
    // RescueMe salt value
    define('SALT', 'SALT');

    // SMS integration constants
    define('SMS_ACCOUNT', '');
    define('SMS_FROM', 'RescueMe');

    // Google API key
    define('GOOGLE_API_KEY', 'GOOGLE_API_KEY');
    
    // RescueMe database constants
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'rescueme');
    define('DB_USERNAME', 'USERNAME');
    define('DB_PASSWORD', 'PASSWORD');

    // RescueMe message constants
    define('TITLE', 'Rescue Me!');
    define('START', 'Start');
    define('LOGON', 'Logg inn');
    define('LOGOUT', 'Logg ut');
    define('ALERT', 'Varsle');
    define('MISSING', 'Savnede');
    define('NEW_MISSING', 'Finn savnet');
    define('USERS', 'Brukere');
    define('NEW_USER', 'Ny bruker');
    define('DASHBOARD', 'Dashboard');
    define('ABOUT', 'Om '.TITLE);
    define('SMS_TEXT', 'TEST: Du er savnet! <br /> Trykk på lenken for at vi skal se hvor du er: <br /> '.APP_URL.'#missing_id');
    define('SMS_NOT_SENT', 'OBS: Varsel ble ikke sendt til "#mb_name"');
    define('SMS2_TEXT', 'Om du har GPS på telefonen, anbefaler vi at du aktiverer dette. Vanligvis finner du dette under Innstillinger -> Generelt, eller Innstillinger -> Plassering');
    
    // Load automatic class loader class
    require('class/SplClassLoader.php');
    
    // Define common class loader name
    define('COMMON_CLASS_LOADER', 'COMMON_CLASS_LOADER');
    
    // Create CIM API class loader instance
    $_SESSION[COMMON_CLASS_LOADER] = new SplClassLoader('RescueMe',__DIR__.DIRECTORY_SEPARATOR.'class');
    
    // Register class loader instance with SPL
    $_SESSION[COMMON_CLASS_LOADER]->register();

