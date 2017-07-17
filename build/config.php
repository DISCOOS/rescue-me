<?php

define('HELP',"help");
define('NAME',"name");
define('ACTION',"action");
define('VERSION',"version");
define('STATUS',"status");
define('IMPORT',"import");
define('SEED',"seed");
define('EXPORT',"export");
define('BASELINE',"baseline");
define('MIGRATE',"migrate");
define('EXTRACT', "extract");
define('PACKAGE',"package");
define('INSTALL',"install");
define('CONFIGURE',"configure");
define('UNINSTALL',"uninstall");
define('ARCHIVE',"archive");
define('SRC_DIR',"src-dir");
define('DB_DIR',"db-dir");
define('BUILD_DIR',"build-dir");
define('DIST_DIR',"dist-dir");
define('EXTRACT_DIR',"extract-dir");
define('INSTALL_DIR',"install-dir");
define('INI_VERSION',"VERSION");
define('PARAM_DB',"db");
define('PARAM_HOST',"host");
define('PARAM_USERNAME',"username");
define('PARAM_PASSWORD',"password");
define('PARAM_VERSION',"v");
define('PARAM_FILE',"file");

define('DEFAULT_LOCALE','en_US');

// Include build resources
require 'inc/build.inc.php';

// Get source path if exists
$src = realpath(implode(DIRECTORY_SEPARATOR,array(dirname(__FILE__),'..','src')));

// Include dependent resources
if($src === false) {

    if(in_phar() === false) {
        fatal('Unexpected build state. Source path not found.');
    }
    $inc = 'inc'.DIRECTORY_SEPARATOR;
    $required = array('common');

} else {
    $inc = implode(DIRECTORY_SEPARATOR,array(dirname(__FILE__),'..','src','inc')).DIRECTORY_SEPARATOR;
    $required = array('common');
}
foreach($required as $lib) {
    require($inc.$lib.'.inc.php');
}