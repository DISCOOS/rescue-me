<?php

use RescueMe\Context;

$verfile = dirname(__FILE__).DIRECTORY_SEPARATOR."VERSION";
    
if(!file_exists($verfile)) {
    require "setup.php";
    die();
}

define('MAINTAIN', false);

define('DEBUG', false);

// Silex routing instead of router.php - still in early development!
define('USE_SILEX', true);

// Allow usage on command line
if(!USE_SILEX && php_sapi_name() !== 'cli') session_start();

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
define('APP_PATH_ABOUT', APP_PATH.'about'.DIRECTORY_SEPARATOR);
define('APP_PATH_NEWS', APP_PATH.'news'.DIRECTORY_SEPARATOR);
define('APP_PATH_HELP', APP_PATH.'help'.DIRECTORY_SEPARATOR);
define('APP_PATH_DATA', APP_PATH.'data'.DIRECTORY_SEPARATOR);
define('APP_PATH_GUI', APP_PATH.'gui'.DIRECTORY_SEPARATOR);
define('APP_PATH_VENDOR', APP_PATH.'vendor'.DIRECTORY_SEPARATOR);

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

// Security constants
define('PASSWORD_LENGTH', 4);

// SMS integration constants
define('SMS_FROM', 'RescueMe');

// RescueMe database constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'rescueme');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

// Set current timezone
if(\RescueMe\TimeZone::set(DEFAULT_TIMEZONE) === FALSE) {
    trigger_error("Failed to set timesone to [" . DEFAULT_TIMEZONE . "]");
}

// Control debugging
use_soap_error_handler(DEBUG);
error_reporting(DEBUG ? ~0 : 0);
ini_set('display_errors', DEBUG ? 1 : 0);

// Load application context - used by classes
Context::load(array(
        Context::APP_PATH => APP_PATH,
        Context::DATA_PATH => APP_PATH_DATA,
        Context::LOCALE_PATH => APP_PATH_LOCALE,
        Context::VENDOR_PATH => APP_PATH_VENDOR,
        Context::URI => APP_URI,
        Context::TITLE => TITLE,
        Context::VERSION => VERSION,
        Context::DB_HOST => DB_HOST,
        Context::DB_NAME => DB_NAME,
        Context::DB_USERNAME => DB_USERNAME,
        Context::DB_PASSWORD => DB_PASSWORD,
        Context::DB_PASSWORD => DB_PASSWORD,
        Context::SECURITY_SALT => SALT,
        Context::SECURITY_PASSWORD_LENGTH => PASSWORD_LENGTH
));
