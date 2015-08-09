<?php

use RescueMe\Admin\Context;

$dir = implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__))).DIRECTORY_SEPARATOR;

require($dir.'config.php');

if(MAINTAIN) {
    require($dir.'maintenance.php');
    die();
}

// RescueMe administration paths
define('ADMIN_PATH', APP_PATH.'admin/');
define('ADMIN_PATH_INC', ADMIN_PATH.'inc/');
define('ADMIN_PATH_GUI', ADMIN_PATH.'gui/');
define('ADMIN_PATH_CLASS', ADMIN_PATH.'classes/');

// Load application context - used by classes
Context::extend(array(
        Context::ADMIN_PATH => ADMIN_PATH,
        Context::ADMIN_URI => ADMIN_URI
));


foreach(array('common', 'gui') as $lib) {
    require(ADMIN_PATH_INC.$lib.'.inc.php');
}