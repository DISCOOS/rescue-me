<?php

use RescueMe\Context;
use RescueMe\TimeZone;

$verfile = dirname(__FILE__).DIRECTORY_SEPARATOR."VERSION";
    
if(!file_exists($verfile)) {
    require "setup.php";
    die();
}

define('MAINTAIN', false);

define('DEBUG', false);

// Allow usage on command line
if(php_sapi_name() !== 'cli') session_start();

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
define('LOCATE_URL', APP_URL.'l/#mobile_id');
define('ADMIN_TRACE_URL', APP_URL.'admin/trace/#mobile_id');

// Include dependent resources
foreach(array('locale', 'common', 'gui') as $lib) {
    require(APP_PATH_INC.$lib.'.inc.php');
}

// RescueMe salt value
define('SALT', '');

// Security constants
define('PASSWORD_LENGTH', 4);

// RescueMe database constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'rescueme');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

// Google API KEYS
define('GOOGLE_MAPS_API_KEY', '');
define('GOOGLE_GEOCODING_API_KEY', '');

// Set current timezone
if(TimeZone::set(DEFAULT_TIMEZONE) === FALSE) {
    trigger_error("Failed to set timesone to [" . DEFAULT_TIMEZONE . "]");
}

// Control debugging
use_soap_error_handler(DEBUG);
error_reporting(DEBUG ? ~0 : 0);
ini_set('display_errors', DEBUG ? 1 : 0);

// Load application context - used by classes
Context::load(array (
    Context::APP_PATH => APP_PATH,
    Context::DATA_PATH => APP_PATH_DATA,
    Context::LOCALE_PATH => APP_PATH_LOCALE,
    Context::VENDOR_PATH => APP_PATH_VENDOR
));
