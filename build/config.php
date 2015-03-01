<?php

    // Define constants
use RescueMe\Context;

define('HELP',"help");
    define('NAME',"name");
    define('ACTION',"action");
    define('VERSION',"version");
    define('STATUS',"status");
    define('IMPORT',"import");
    define('EXPORT',"export");
    define('EXTRACT', "extract");
    define('PACKAGE',"package");
    define('INSTALL',"install");
    define('CONFIGURE',"configure");
    define('UNINSTALL',"uninstall");
    define('ARCHIVE',"archive");
    define('SRC_DIR',"src-dir");
    define('IMPORT_DIR',"import-dir");
    define('EXPORT_DIR',"export-dir");
    define('BUILD_DIR',"build-dir");
    define('DIST_DIR',"dist-dir");
    define('EXTRACT_DIR',"extract-dir");
    define('INSTALL_DIR',"install-dir");
    define('PARAM_DB',"db");
    define('PARAM_HOST',"host");
    define('PARAM_USERNAME',"username");
    define('PARAM_PASSWORD',"password");

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