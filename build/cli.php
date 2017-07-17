<?php

/**
 * RescueMe Package command line interface
 *
 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 19. June 2013
 *
 * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
 */

// Only run this when executed on the CLI
use RescueMe\Context;
use RescueMe\DB;

if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {

    // Configure build
    require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

    // Get options
    $opts = parse_opts($argv, array('h'));

    // Get action
    $action = $opts[ACTION];

    // Perform sanity checks on php host system
    $halt = false;
    $status = system_checks($action);
    if($status !== true) {
        foreach($status as $error) {
            list($code, $message) = $error;
            if(is_array($message)) {
                $message = implode(PHP_EOL.'--> ',$message);
            }
            error($message);
            if($code === E_USER_ERROR) {
                $halt = true;
            }
        }
    }

    // Failure?
    if($halt !== false) {
        echo PHP_EOL;
        exit();
    }

    // Get error message?
    $msg = (empty($action) ? "Show help: -h | help ACTION" : null);

    // Print help now?
    if(empty($action) && isset($opts['h'])) print_help();

    // Print help with error message?
    if(isset($msg))  print_help(HELP, "Show help: -h");

    info("rescueme build script", BUILD_SUCCESS);
    info("  usage: -h"); echo PHP_EOL;

    execute(array($action => $opts));

}

else {

    fatal("Run 'cli' on php-cli only!");

}// else


/**
 * Execute actions
 *
 * @param array $actions
 */
function execute($actions)
{
    $keys = array
    (
        'SALT', 'TITLE', 'COUNTRY_PREFIX', 'DEFAULT_LOCALE',
        'DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD', 'DEFAULT_TIMEZONE',
        'DEBUG', 'MAINTAIN'
    );

    foreach($actions as $action => $opts)
    {
        // Print help now?
        if(isset($opts['h'])) {
            print_help($action);
        }

        // Perform action
        switch($action)
        {
            case STATUS:

                begin(STATUS);

                // Configure dependencies and get install path
                $root = configure($opts, INSTALL_DIR, 'src');

                // Get configuration parameters
                $config = file_exists("$root/config.php") ? get_config_params($root, $keys) : array();

                // Get arguments
                $args = get_db_params($opts, $config);

                require('classes/RescueMe/Status.php');

                // Create status command
                $status = new RescueMe\Status($root, $args);

                // Status unsuccessful?
                if(($config = $status->execute($keys)) === false) {
                   done(STATUS, BUILD_ERROR);
                } else {
                    done(STATUS);
                }

                break;

            case IMPORT:

                // Skip?
                if(in_phar()) print_help();

                begin(IMPORT);

                // Configure dependencies and get source path
                $src = configure($opts, SRC_DIR, 'src');

                // Get configuration parameters
                $config = file_exists("$src/config.php") ? get_config_params($src, $keys) : array();

                // Get current locale
                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);

                // Get ini values
                $ini = get_ini($src, $locale, $keys);

                // Get arguments
                $args = array_merge($ini, $config, get_db_params($opts, $config));

                // Get database import file
                $file = realpath($opts, PARAM_FILE, implode(DIRECTORY_SEPARATOR,array($src,'db','init.sql')));

                require('classes/RescueMe/Import.php');

                // Create import command
                $import = new RescueMe\Import($file, $args);

                // Import unsuccessful?
                if($import->execute() !== true) {
                   done(IMPORT, BUILD_ERROR);
                } else {
                   done(IMPORT);
                }

                break;

            case SEED:

                // Skip?
                if(in_phar()) print_help();

                begin(SEED);

                // Configure dependencies and get source path
                $src = configure($opts, SRC_DIR, 'src');

                // Get configuration parameters
                $config = file_exists("$src/config.php") ? get_config_params($src, $keys) : array();

                // Get current locale
                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);

                // Get ini values
                $ini = get_ini($src, $locale, $keys);

                // Get arguments
                $args = array_merge($ini, $config, get_db_params($opts, $config));

                // Get database directory root
                $root = get_safe_dir($opts, DB_DIR, $src.DIRECTORY_SEPARATOR."db");

                // Get current locale
                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);

                // Enable locale strings are handled during install
                define_locale($src, $locale);

                require('classes/RescueMe/Seed.php');

                // Create import command
                $seed = new RescueMe\Seed($root, $args);

                // Import unsuccessful?
                if($seed->execute() !== true) {
                    done(SEED, BUILD_ERROR);
                } else {
                    done(SEED);
                }

                break;

            case MIGRATE:

                // Skip?
                if(in_phar()) print_help();

                begin(MIGRATE);

                // Configure dependencies and get source path
                $src = configure($opts, SRC_DIR, 'src');

                // Get configuration parameters
                $config = file_exists("$src/config.php") ? get_config_params($src, $keys) : array();

                // Get current locale
                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);

                // Get ini values
                $ini = get_ini($src, $locale, $keys);

                // Get arguments
                $args = array_merge($ini, $config, get_db_params($opts, $config));

                // Get database directory root
                $db = get_safe_dir($opts, DB_DIR, $src.DIRECTORY_SEPARATOR."db");

                // Enable locale strings are handled during install
                define_locale($src, $locale);

                require('classes/RescueMe/Migrate.php');

                // Create import command
                $seed = new RescueMe\Migrate($src, $db, $args);

                // Import unsuccessful?
                if($seed->execute() !== true) {
                    done(MIGRATE, BUILD_ERROR);
                } else {
                    done(MIGRATE);
                }

                break;

            // TODO: Fix export that create tables in order that ensures all foreign keys exists before constraints
//            case EXPORT:
//
//                // Skip?
//                if(in_phar()) print_help();
//
//                begin(EXPORT);
//
//                // Configure dependencies and get source path
//                $src = configure($opts, SRC_DIR, 'src');
//
//                // Get database directory root
//                $db = get_safe_dir($opts, DB_DIR, $src.DIRECTORY_SEPARATOR."db");
//
//                // Get configuration parameters
//                $config = file_exists(realpath($src)."/config.php") ? get_config_params($src, $keys) : array();
//
//                // Get current locale
//                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);
//
//                // Get ini values
//                $ini = get_ini($src, $locale, $keys);
//
//                // Get database parameters
//                $ini = array_merge($ini, get_db_params($opts, $config));
//
//                require('classes/RescueMe/Export.php');
//
//                // Create import command
//                $export = new RescueMe\Export($db, $ini);
//
//                // Export unsuccessful?
//                if($export->execute() !== true) {
//                   done(EXPORT, BUILD_ERROR);
//                } else {
//                    done(EXPORT);
//                }
//
//                break;

            case BASELINE:

                // Skip?
                if(in_phar()) print_help();

                begin(BASELINE);

                // Verify options
                $msg = (isset($opts[PARAM_VERSION]) ? null : "VERSION is missing");

                // Print help now?
                if(!empty($msg)) print_help(BASELINE, $msg);

                // Configure dependencies and get source path
                $src = configure($opts, SRC_DIR, 'src');

                // Get database directory root
                $db = get_safe_dir($opts, DB_DIR, $src.DIRECTORY_SEPARATOR."db");

                // Get configuration parameters
                $config = file_exists("$src/config.php") ? get_config_params($src, $keys) : array();

                // Get current locale
                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);

                // Get ini values
                $ini = get_ini($src, $locale, $keys);

                // Get arguments
                $args = array_merge($ini, $config, get_db_params($opts, $config));

                require('classes/RescueMe/Baseline.php');

                // Create import command
                $baseline = new RescueMe\Baseline($db, $args);

                // Baseline unsuccessful?
                if($baseline->execute() !== true) {
                   done(BASELINE, BUILD_ERROR);
                } else {
                    done(BASELINE);
                }

                break;

            case PACKAGE:

                // Skip?
                if(in_phar()) print_help();

                begin(PACKAGE);

                // Verify options
                $msg = (isset($opts[PARAM_VERSION]) ? null : "VERSION is missing");

                // Print help now?
                if(!empty($msg)) print_help(PACKAGE, $msg);

                // Get default paths
                $src = get_safe_dir($opts, SRC_DIR, "src");
                $build = get_safe_dir($opts, BUILD_DIR, "build");
                $dist = get_safe_dir($opts, DIST_DIR, "dist", false);

//                // Export database structure?
//                if(stristr(isset_get($opts, EXPORT, 'false'), 'true') !== false)
//                {
//                    execute(array(EXPORT => array(SRC_DIR => $src, DB_DIR => $src.DIRECTORY_SEPARATOR."db")));
//                }

                // Baseline database?
                if(stristr(isset_get($opts, BASELINE, 'true'), 'true') !== false)
                {
                    execute(array(BASELINE => array(
                            SRC_DIR => $src,
                            DB_DIR => $src.DIRECTORY_SEPARATOR."db",
                            PARAM_VERSION => $opts[PARAM_VERSION]
                        ))
                    );
                }

                require('classes/RescueMe/Package.php');

                // Create package command
                $package = new RescueMe\Package($opts[PARAM_VERSION], $build, $src, $dist);

                // Package unsuccessful?
                if($package->execute() !== true) {
                   done(PACKAGE, BUILD_ERROR);
                } else {
                    done(PACKAGE);
                }

                break;

            case EXTRACT:

                // Skip?
                if(in_phar() === false) print_help();

                begin(EXTRACT);

                // Get parameters
                $src = get($opts, ARCHIVE, "src.zip");
                $root = get($opts, EXTRACT_DIR, getcwd());

                require('classes/RescueMe/Extract.php');

                // Create extract command
                $extract = new RescueMe\Extract($src, $root);

                // Execute extraction
                if($extract->execute() !== true) {
                    done(EXTRACT, BUILD_ERROR);
                } else {
                    done(EXTRACT);
                }

                break;

            case INSTALL:
            case CONFIGURE:

                // Skip?
                if(in_phar() && $action === CONFIGURE || in_phar() == false && $action === INSTALL) print_help();

                begin($action);

                // Get installation directory
                $src = get_safe_dir($opts, INSTALL_DIR, in_phar() ? getcwd() : "src", false);

                // Get configuration parameters
                $config = file_exists("$src/config.php") ? get_config_params($src, $keys) : array();

                // Get flags
                $init = filter_var(get($opts, 'init', 'true'), FILTER_VALIDATE_BOOLEAN);
                info(sprintf('  Initialize modules: %s', $init ? 'Yes' : 'No'));
                $silent = filter_var(get($opts, 'silent', 'false'), FILTER_VALIDATE_BOOLEAN);
                info(sprintf('  Silent install: %s', $silent ? 'Yes' : 'No'));
                $update = filter_var(get($opts, 'update', 'false'), FILTER_VALIDATE_BOOLEAN);
                if(!in_phar()) {
                    info(sprintf('  Update dependencies: %s', $update ? 'Yes' : 'No'));
                }
                echo PHP_EOL;

                // Get current locale
                $locale = (extension_loaded("intl") ? \locale_get_default() : DEFAULT_LOCALE);

                // Get ini values
                $ini = ensure_ini($silent, get_ini($src, $locale, $keys));

                // Install only?
                if($action === INSTALL) {

                    // Uninstall?
                    if(file_exists(realpath($src)))
                    {
                        execute(array(UNINSTALL => array(INSTALL_DIR => $src)));
                    }

                    // Get source archive
                    $zip = get($opts, ARCHIVE, "src.zip");

                    // Extract
                    execute(array(EXTRACT => array(ARCHIVE => $zip, EXTRACT_DIR => $src)));

                }

                // MUST be performed AFTER extract because install must exists before real path can be resolved
                $src = configure($opts, INSTALL_DIR, $src);

                // Enable locale strings are handled during install
                define_locale($src, $locale);

                // Get database directory root
                $db = get_safe_dir($opts, DB_DIR, $src.DIRECTORY_SEPARATOR."db");

                // Get database import file
                $file = isset_get($opts, PARAM_FILE, implode(DIRECTORY_SEPARATOR,array($src,'db','init.sql')));

                // Get arguments
                $args = array_merge($ini, $config, get_db_params($opts, $ini));

                require('classes/RescueMe/Import.php');
                $script = new RescueMe\Import($file, $args);
                if($script->execute() !== true) {
                    done($action, BUILD_ERROR); break;
                }// if

                // Migrate before seeding?
                if(DB::legacyVersion() || $args[PARAM_VERSION] !== DB::latestVersion()) {

                    require('classes/RescueMe/Migrate.php');
                    $script = new RescueMe\Migrate($src, $db, $args);
                    if($script->execute() !== true) {
                        done($action, BUILD_ERROR); break;
                    }// if

                    // Update version argument to latest version
                    $args[PARAM_VERSION] = get_version($src);

                }

                // Seed database with data
                require('classes/RescueMe/Seed.php');
                $script = new RescueMe\Seed($db, $args, $silent);
                if($script->execute() !== true) {
                    done($action, BUILD_ERROR); break;
                }// if

                // Install configuration, libraries and modules
                require('classes/RescueMe/Install.php');
                $install = new RescueMe\Install($src, $args, $silent, $init, $update);
                if($install->execute() !== true) {
                    done($action, BUILD_ERROR); break;
                } else {
                    done($action);
                }


                break;

            case UNINSTALL:

                // Skip?
                if(in_phar() === false) print_help();

                begin(UNINSTALL);

                // Import classes
                require('classes/RescueMe/Uninstall.php');

                // Get default path install path
                $root = get_safe_dir($opts, INSTALL_DIR, getcwd());

                $uninstall = new RescueMe\Uninstall($root);

                // Uninstall successful?
                if($uninstall->execute() !== true) {
                   done($action, BUILD_ERROR);
                } else {
                    done($action);
                }

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

    }// foreach

}// execute


/**
 * @param $root
 * @param $locale
 */
function define_locale($root, $locale)
{
    // TODO: Move all constants from config.php to Context
    define('APP_PATH', $root . DIRECTORY_SEPARATOR);
    define('APP_PATH_LOCALE', APP_PATH . 'locale' . DIRECTORY_SEPARATOR);

    require(implode(DIRECTORY_SEPARATOR, array($root, 'inc', 'locale.inc.php')));

    set_system_locale(DOMAIN_ADMIN, $locale);
}// define_locale


/**
 * @param $root
 * @param $locale
 * @param $keys
 * @return array
 */
function get_ini($root, $locale, $keys)
{
    // Get default ini values
    $ini = in_phar() ? parse_ini_file("rescueme.ini") : array();

    // Get version
    $ini[INI_VERSION] = isset_get($ini, INI_VERSION, get_version($root));

    // Get host specific defaults
    $codes = preg_split("#[_-]#", $locale);
    $ini['COUNTRY_PREFIX'] = isset($codes[1]) ? $codes[1] : 'US';

    // Get default configuration parameters
    $config = get_config_params($root, $keys);
    $ini = array_merge($ini, $config);

    // Get default minify configuration parameters
    $config = get_config_minify_params($root);
    $ini = array_merge($ini, $config);

    return $ini;

}// get_ini


/**
 * @param $root
 * @return string
 */
function get_version($root="src")
{
    // Get configured version
    $verfile = $root . DIRECTORY_SEPARATOR . "VERSION";
    $version = file_exists(realpath($verfile)) ? file_get_contents($verfile) : get_current_git_branch();

    return $version;

}// get_version

/**
 * @param $silent
 * @param $ini
 * @return mixed
 */
function ensure_ini($silent, $ini)
{
    // Prompt params from user?
    if ($silent === false) {

        $states = array(
            'ON' => 1,
            'TRUE' => 1,
            'T' => 1,
            'OFF' => 0,
            'FALSE' => 0,
            'F' => 0
        );

        $ini['SALT'] = in("  Salt", get($ini, "SALT", str_rnd()));
        $ini['TITLE'] = in("  Title", get($ini, "TITLE", "RescueMe"));
        $ini['DB_HOST'] = in("  DB Host", get($ini, "DB_HOST", "localhost"));
        $ini['DB_NAME'] = in("  DB Name", get($ini, "DB_NAME", "rescueme"));
        $ini['DB_USERNAME'] = in("  DB Username", get($ini, "DB_USERNAME", "root"));
        $ini['DB_PASSWORD'] = in("  DB Password", get($ini, "DB_PASSWORD", "''"));
        $ini['COUNTRY_PREFIX'] = strtoupper(in("  Default Country Code (ISO2)", get($ini, "COUNTRY_PREFIX")));
        $ini['DEFAULT_LOCALE'] = in("  Default Language (locale, ISO2)", get($ini, "DEFAULT_LOCALE"));
        $ini['DEFAULT_TIMEZONE'] = in_timezone($ini);
        $ini['MINIFY_MAXAGE'] = in("  Minify Cache Time", get($ini, "MINIFY_MAXAGE", 1800, false));

        // System states
        $debug = get($ini, "DEBUG") ? 'ON' : 'OFF';
        $maintain = get($ini, "MAINTAIN") ? 'ON' : 'OFF';
        $ini['DEBUG'] = (bool)in("  System Debug State", $debug, NEWLINE_NONE, true, true, $states, true);
        $ini['MAINTAIN'] = (bool)in("  System Maintenance State", $maintain, NEWLINE_NONE, true, true, $states, true);

    } else {

        $ini['SALT'] = get($ini, "SALT", str_rnd());
        $ini['TITLE'] = get($ini, "TITLE", "RescueMe");
        $ini['DB_HOST'] = get($ini, "DB_HOST", "localhost");
        $ini['DB_NAME'] = get($ini, "DB_NAME", "rescueme");
        $ini['DB_USERNAME'] = get($ini, "DB_USERNAME", "root");
        $ini['DB_PASSWORD'] = get($ini, "DB_PASSWORD", "''");
        $ini['COUNTRY_PREFIX'] = get($ini, "COUNTRY_PREFIX");
        $ini['DEFAULT_LOCALE'] = get($ini, "DEFAULT_LOCALE");
        $ini['DEFAULT_TIMEZONE'] = get($ini, "DEFAULT_TIMEZONE");

        // System states
        $ini['DEBUG'] = get($ini, "DEBUG");
        $ini['MAINTAIN'] = get($ini, "MAINTAIN");

    }

    echo PHP_EOL;

    return $ini;

}// ensure_opts


/**
 * Configure dependencies
 *
 * @param array $opts Options
 * @param string $name Root path option name
 * @param string $default Default root path
 * @param bool $real Resolve to real path (must exists)
 *
 * @return string Source directory
 */
function configure($opts, $name, $default, $real = true) {

    // Get default paths
    $root = get_safe_dir($opts, $name, $default, $real);
    $data = $root.DIRECTORY_SEPARATOR.'data';
    $vendor = $root.DIRECTORY_SEPARATOR.'vendor';
    $locale = $root.DIRECTORY_SEPARATOR.'locale';

    // Import class loaders?
    if(in_phar()) {
        if(require_once('classes/ClassLoader.php')) {
            info('   Configuring phar...');
            $loader = new \Composer\Autoload\ClassLoader();
            info('     Registered PSR4 class autoloading');
            // Add classes in phar
            $loader->addPsr4('RescueMe\\',implode(DIRECTORY_SEPARATOR, array('classes','RescueMe')));
            // Add classes in source (only loadable after extract is performed)
            $loader->addPsr4('RescueMe\\',implode(DIRECTORY_SEPARATOR, array(realpath($root),'classes','RescueMe')));
            $loader->addPsr4('RescueMe\\',implode(DIRECTORY_SEPARATOR, array(realpath($root),'admin','classes','RescueMe')));
            $loader->addPsr4('RescueMe\\',implode(DIRECTORY_SEPARATOR, array(realpath($root),'sms','classes','RescueMe')));
            $loader->addPsr4('RescueMe\\',implode(DIRECTORY_SEPARATOR, array(realpath($root),'sms','classes','RescueMe')));
            $loader->addPsr4('Psr\\',implode(DIRECTORY_SEPARATOR, array(realpath($root),'vendor','psr','log','Psr')));
            $loader->add('WURFL',implode(DIRECTORY_SEPARATOR, array(realpath($root),'vendor','wurfl','wurfl-api')));
            $loader->register();
            info('   Configuring phar...DONE');
        }
    }
    else {
        require($vendor.DIRECTORY_SEPARATOR.'autoload.php');
    }

    // Load application context
    Context::load(array (
        Context::APP_PATH => $root,
        Context::DATA_PATH => $data,
        Context::LOCALE_PATH => $locale,
        Context::VENDOR_PATH => $vendor
    ));

    return $root;
}


/**
 * Print help.
 *
 * Peforms a forced exit.
 *
 * @param string $action Action
 * @param string $msg Message
 * @param int $status Exit status
 */
function print_help($action = HELP, $msg = null, $status = BUILD_ERROR)
{
    switch($action)
    {
        case STATUS:

            info("RescueMe Status Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme status [OPTIONS]' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --install-dir Installation directory [default: src]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case IMPORT:

            // Skip?
            if(in_phar()) print_help();

            info("RescueMe Database Import Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme import [OPTIONS]' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        -v            Database version [default: ".get_version()."]" . PHP_EOL;
            echo "        --host        Database host" . PHP_EOL;
            echo "        --db          Database name" . PHP_EOL;
            echo "        --username    Database username" . PHP_EOL;
            echo "        --password    Database password" . PHP_EOL;
            echo "        --file        Database sql file [default: src/db/init.sql]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case SEED:

            // Skip?
            if(in_phar()) print_help();

            info("RescueMe Database Seed Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme seed [OPTIONS]' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --host        Database host" . PHP_EOL;
            echo "        --db          Database name" . PHP_EOL;
            echo "        --username    Database username" . PHP_EOL;
            echo "        --password    Database password" . PHP_EOL;
            echo "        --db-dir      Database directory [default: src/db]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case MIGRATE:

            // Skip?
            if(in_phar()) print_help();

            info("RescueMe Database Migrate Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme migrate [OPTIONS]' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --host        Database host" . PHP_EOL;
            echo "        --db          Database name" . PHP_EOL;
            echo "        --username    Database username" . PHP_EOL;
            echo "        --password    Database password" . PHP_EOL;
            echo "        --db-dir      Database directory [default: src/db]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
//        case EXPORT:
//
//            // Skip?
//            if(in_phar()) print_help();
//
//            info("RescueMe Database Export Script" . (isset($msg) ? " - " . $msg : ""));
//            echo 'Usage: rescueme export [OPTIONS]' . PHP_EOL;
//            echo "OPTIONS:" . PHP_EOL;
//            echo "        --host        Database host" . PHP_EOL;
//            echo "        --db          Database name" . PHP_EOL;
//            echo "        --username    Database username" . PHP_EOL;
//            echo "        --password    Database password" . PHP_EOL;
//            echo "        --db-dir      Database export directory [default: src/db]" . PHP_EOL;
//            echo "        --src-dir     Source directory [default: src]" . PHP_EOL;
//            echo "        -h            Display this help" . PHP_EOL;
//            break;

        case BASELINE:

            // Skip?
            if(in_phar()) print_help();

            info("RescueMe Database Baseline Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme baseline [OPTIONS]' . PHP_EOL;
            echo "PARAMETERS:" . PHP_EOL;
            echo "        -v            Version" . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --db-dir      Database directory [default: src/db]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;

        case EXTRACT:

            // Skip?
            if(in_phar() === false) print_help();

            info("RescueMe Extraction Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme extract [OPTIONS]' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --archive     Archive [default: src.zip]" . PHP_EOL;
            echo "        --extract-dir Extraction directory [default: ".  getcwd() ."]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case PACKAGE:

            // Skip?
            if(in_phar()) print_help();

            info("RescueMe Package Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme package -v VERSION [OPTIONS]' . PHP_EOL;
            echo "PARAMETERS:" . PHP_EOL;
            echo "        -v            Version" . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
//            echo "        --export      Export RescueMe database [default: false]" . PHP_EOL;
            echo "        --baseline    Baseline RescueMe database [default: true]" . PHP_EOL;
            echo "        --src-dir     Source directory [default: src]" . PHP_EOL;
            echo "        --db-dir      Database directory [default: src/db]" . PHP_EOL;
            echo "        --dist-dir    Package distribution directory [default: dist]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case INSTALL:

            // Skip?
            if(in_phar() === false) print_help();

            info("RescueMe Install Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme install [OPTIONS]... ' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --silent      No user interaction [use defaults]" . PHP_EOL;
            echo "        --init        Initialize modules (long operation) [default: true]" . PHP_EOL;
            echo "        --archive     RescueMe archive file [default: src.zip]" . PHP_EOL;
            echo "        --install-dir Install directory [default: ".getcwd()."]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case CONFIGURE:

            // Skip?
            if(in_phar()) print_help();

            info("RescueMe Configure Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme configure [OPTIONS]... ' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --silent      No user interaction [use defaults]" . PHP_EOL;
            echo "        --init        Initialize modules (long operation) [default: true]" . PHP_EOL;
            echo "        --update      Update libraries if already installed [default: false]" . PHP_EOL;
            echo "        --install-dir Install directory [default: src]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case UNINSTALL:

            // Skip?
            if(in_phar() === false) print_help();

            info("RescueMe Uninstall Script" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme uninstall [OPTIONS]... ' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        --install-dir Install directory [default: ".getcwd()."]" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            break;
        case HELP:
        default:
            info("RescueMe Command Line Scrips" . (isset($msg) ? " - " . $msg : ""));
            echo 'Usage: rescueme ACTION... [OPTIONS]... ' . PHP_EOL;
            echo "OPTIONS:" . PHP_EOL;
            echo "        -h            Display this help" . PHP_EOL;
            echo "ACTION:" . PHP_EOL;
            echo "        status        Show RescueMe status (parameters)" . PHP_EOL;
            if(in_phar()) {
                echo "        extract       Extract RescueMe" . PHP_EOL;
                echo "        install       Install RescueMe" . PHP_EOL;
                echo "        uninstall     Uninstall RescueMe" . PHP_EOL;
            }
            else {
                echo "        import        Import RescueMe database (sql->db)" . PHP_EOL;
                echo "        seed          Seed RescueMe database (sql->db)" . PHP_EOL;
                echo "        migrate       Migrate RescueMe database (sql->db)" . PHP_EOL;
//                echo "        export        Export RescueMe database (db->sql)" . PHP_EOL;
                echo "        baseline      Baseline RescueMe database (init.sql->baselines/{version}.sql)" . PHP_EOL;
                echo "        configure     Configure RescueMe source (dev)" . PHP_EOL;
                echo "        package       Package RescueMe as executable phar-archive" . PHP_EOL;
            }
            echo "        help          Display help about an action" . PHP_EOL;

            break;
    }// switch

    // Finished
    echo PHP_EOL . PHP_EOL;

    exit($status);

}// print_help

