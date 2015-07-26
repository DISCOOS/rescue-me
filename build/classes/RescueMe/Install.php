<?php

/**
 * File containing: Install class
 *
 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 19. June 2013
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe;

use RescueMe\Domain\Roles;
use RescueMe\Domain\User;

/**
 * Install class
 *
 * @package
 */
class Install {

    /**
     * Installation root
     * @var string
     */
    private $root;


    /**
     * Installation ini values
     * @var array
     */
    private $ini;


    /**
     * Use defaults if TRUE, prompt otherwise.
     * @var boolean
     */
    private $silent;


    /**
     * Init components
     * @var boolean|array
     */
    private $init;


    /**
     * Update components
     * @var boolean|array
     */
    private $update;


    /**
     * Constructor
     *
     * @param string $root Installation root
     * @param array $ini Installation ini parameters
     * @param boolean $silent Use defaults if TRUE, prompt otherwise.
     * @param boolean|array $init Initialize components after installation.
     * @param boolean|array $update Update components if already installed.
     *
     *
     * @since 19. June 2013
     *
     */
    public function __construct($root, $ini, $silent, $init, $update)
    {
        $this->root = $root;
        $this->ini = $ini;
        $this->silent = $silent;
        $this->init = $init;
        $this->update = $update;

    }// __construct


    /**
     * Execute install script
     *
     * @return mixed TRUE if success, error message otherwise.
     *
     */
    public function execute()
    {
        begin(in_phar() ? INSTALL : CONFIGURE);

        // In dev-mode?
        if (in_phar() === false) {

            $this->initComposer();

            $this->initLibs();

        }

        $this->initConfig();

        $this->initDB();

        $this->initModules();

        $this->initMinify();

        // Create VERSION file
        if (file_put_contents($this->root . DIRECTORY_SEPARATOR . "VERSION", $this->ini['VERSION']) === FALSE) {
            return error(sprintf(VERSION_NOT_SET, $this->ini['VERSION']));
        }

        done(in_phar() ? INSTALL : CONFIGURE);

        // Finished
        return true;


    }// execute


    private function initConfig()
    {
        // Get template from phar?
        if (in_phar()) {
            $config = file_get_contents("config.tpl.php");
            $config_minify = file_get_contents("config.minify.tpl.php");
        } // Get template from source?
        else {
            $config = file_get_contents($this->root . DIRECTORY_SEPARATOR . "config.tpl.php");
            $config_minify = file_get_contents($this->root . DIRECTORY_SEPARATOR . "config.minify.tpl.php");
        }

        // Get config template
        $config = replace_define_array($config, array
        (
            'SALT' => $this->ini['SALT'],
            'TITLE' => $this->ini['TITLE'],
            'SMS_FROM' => $this->ini['SMS_FROM'],
            'DB_HOST' => $this->ini['DB_HOST'],
            'DB_NAME' => $this->ini['DB_NAME'],
            'DB_USERNAME' => $this->ini['DB_USERNAME'],
            'DB_PASSWORD' => $this->ini['DB_PASSWORD'],
            'COUNTRY_PREFIX' => $this->ini['COUNTRY_PREFIX'],
            'DEFAULT_LOCALE' => $this->ini['DEFAULT_LOCALE'],
            'DEFAULT_TIMEZONE' => $this->ini['DEFAULT_TIMEZONE'],
            'DEBUG' => $this->ini['DEBUG'],
            'MAINTAIN' => $this->ini['MAINTAIN']
        ));

        // Create config.php
        if (file_put_contents($this->root . DIRECTORY_SEPARATOR . "config.php", $config) === FALSE) {
            return error(CONFIG_NOT_CREATED);
        }

        // Get config minify template
        $config_minify = replace_define_array($config_minify, array
        (
            'MINIFY_MAXAGE' => $this->ini['MINIFY_MAXAGE']
        ));

        // Create config.php
        if (file_put_contents($this->root . DIRECTORY_SEPARATOR . "config.minify.php", $config_minify) === FALSE) {
            return error(CONFIG_MINIFY_NOT_CREATED);
        }

        // Create apache logs folder
        if (!file_exists(realpath($this->root . DIRECTORY_SEPARATOR . "logs"))) {
            mkdir($this->root . DIRECTORY_SEPARATOR . "logs");
        }

        // Include dependent resources
        $root = $this->root . DIRECTORY_SEPARATOR . 'config.php';

    }


    public function initComposer() {

        $inline = true;
        info("  Installing composer...", BUILD_INFO, NEWLINE_NONE);

        $composer = $this->root . DIRECTORY_SEPARATOR . "composer.phar";

        if(realpath($composer) === false) {

            $installer = 'https://getcomposer.org/installer';

            // Ensure correct working path
            $oldPath = getcwd();
            chdir($this->root . DIRECTORY_SEPARATOR);

            $cmd = 'php -r "eval(\'?>\'.file_get_contents(\'' . $installer . '\'));"';
            exec($cmd, $output, $retval);

            // Restore old path
            chdir($oldPath);

            if ($retval !== 0) {
                $output = implode("\n", $output);
                return error("Failed to download composer: \n$output\n");
            }

            $inline = false;

        }
        info($inline ? "SKIPPED" : " DONE");
    }


    public function initLibs(){

        $vendor = $this->root . DIRECTORY_SEPARATOR . "vendor";

        if(realpath($vendor)) {

            info("  Updating libraries...", BUILD_INFO, NEWLINE_NONE);

            if ($this->is($this->update,'lib')) {

                info('');

                $composer = $this->root . DIRECTORY_SEPARATOR . "composer.phar";

                $cmd = "php $composer update --no-scripts --working-dir='$this->root'";
                exec($cmd, $output, $retval);
                if ($retval !== 0) {
                    $output = implode("\n", $output);
                    return error("Failed to update libraries: \n$output\n");
                }

                info("  Updating libraries...DONE");

            } else {

                info("SKIPPED");
            }

        } else {

            info("  Installing libraries...", BUILD_INFO, NEWLINE_NONE);

            if ($this->is($this->init,'lib')) {

                info('');
                $composer = $this->root . DIRECTORY_SEPARATOR . "composer.phar";

                $cmd = "php $composer install --no-scripts --working-dir='$this->root'";
                exec($cmd, $output, $retval);
                if ($retval !== 0) {
                    $output = implode("\n", $output);
                    return error("Failed to install libraries: \n$output\n");
                }

                info("  Installing libraries...DONE");

            } else {

                info("SKIPPED");
            }
        }
    }


    private function initDB(){

        // Install database
        $name = get($this->ini, 'DB_NAME', null, false);
        if (!defined('DB_NAME')) {
            // RescueMe database constants
            define('DB_NAME', $name);
            define('DB_HOST', get($this->ini, 'DB_HOST', null, false));
            define('DB_USERNAME', get($this->ini, 'DB_USERNAME', null, false));
            define('DB_PASSWORD', get($this->ini, 'DB_PASSWORD', null, false));
        }

        info("  Creating database [$name]...", BUILD_INFO, NEWLINE_NONE);
        if (DB::create($name) === FALSE) {
            return error(sprintf(DB_NOT_CREATED, "$name") . " (check database credentials)");
        }
        info("DONE");

        info("  Importing [rescueme.sql]...", BUILD_INFO, NEWLINE_NONE);
        if (($executed = DB::import($this->root . DIRECTORY_SEPARATOR . "rescueme.sql")) === FALSE) {
            return error(sprintf(DB_NOT_IMPORTED, "rescueme.sql") . " (" . DB::error() . ")");
        }
        info("DONE");

        info("  Initializing database...");

        $skipped = true;

        if (User::isEmpty()) {

            if (!defined('SALT')) {
                define('SALT', get($this->ini, 'SALT', null, false));
            }
            if (!defined('SMS_FROM')) {
                define('SMS_FROM', get($this->ini, 'SMS_FROM', 'RescueMe', false));
            }

            $fullname = in("    Admin Full Name");
            $username = in("    Admin Username (e-mail)");
            $password = in("    Admin Password");
            $country = strtoupper(in("Default Country Code (ISO2)", trim($this->ini["COUNTRY_PREFIX"],'\'"')));
            $mobile = in("    Admin Phone Number Without Int'l Dial Code");

            $user = User::create($fullname, $username, $password, $country, $mobile, 1);
            if ($user === FALSE) {
                return error(ADMIN_NOT_CREATED . " (" . DB::error() . ")");
            }

            $skipped = false;

        }

        // Prepare role permissions
        if (($count = Roles::prepare(1, 0)) > 0) {
            info("    Added $count administrator permissions...OK");
            $skipped = false;
        }
        if (($count = Roles::prepare(2, 0)) > 0) {
            info("    Added $count operator permissions...OK");
            $skipped = false;
        }
        if (($count = Roles::prepare(3, 0)) > 0) {
            info("    Added $count personnel permissions...OK");
            $skipped = false;
        }


        // Ensure user 1 is in the administrator group
        if (Roles::has(1, 1)) {
            info("    Add user 1 to administrator group...SKIPPED");
        } elseif(Roles::grant(1, 1)) {
            info("    Add user 1 to administrator group...OK");
            $skipped = false;
        } else {
            error("    Add user 1 to administrator group...FAILED");
        }

        info("  Initializing database..." . ($skipped ? 'SKIPPED' : 'DONE'));

    }


    private function initModules(){

        $inline = true;
        info("  Initializing modules...", BUILD_INFO);

        $callback = function($progress) use ($inline) {
            info("    $progress", BUILD_INFO);
        };

        $init = $this->is($this->init,'module');
        $update = $this->is($this->update,'module');

        if (Manager::install($init, $update, $callback) !== false) {
            info("    System modules installed", BUILD_INFO);
            $inline = false;
        }

        // Prepare user modules
        $users = User::getAll();
        if ($users !== false) {
            /** @var User $user */
            foreach ($users as $user) {
                if (Manager::prepare($user->id, false)) {
                    info("    Modules for [$user->name] installed", BUILD_INFO, $inline ? NEWLINE_BOTH : NEWLINE_POST);
                    $inline = false;
                }
            }
        }
        info($inline ? "SKIPPED" : "  Initializing modules...DONE");

    }// initModules


    private function initMinify() {

        $inline = true;
        info("  Initializing minify...", BUILD_INFO, NEWLINE_NONE);

        $cache = $this->root . DIRECTORY_SEPARATOR . "min" . DIRECTORY_SEPARATOR . "cache";
        if (realpath($cache) === false) {
            if (mkdir($cache) === false) {
                return error(sprintf(DIR_NOT_CREATED, $cache));
            }
            $cache = realpath($this->root . DIRECTORY_SEPARATOR . "min" . DIRECTORY_SEPARATOR . "cache");
            if(is_linux() || is_osx()) {
                if (!is_sudo()) {
                    rmdir($cache);
                    return error(NOT_SUDO);
                }
                $user = "www-data:www-data";
                if ($this->silent === false) {
                    $user = in("    Webservice username", $user, NEWLINE_PRE);
                    $inline = false;
                }
                $user = explode(":", $user);
                if (chown($cache, $user[0]) === false) {
                    rmdir($cache);
                    return error(sprintf(CHOWN_NOT_SET, $user[0], $cache));
                }
                if (isset($user[1]) && chgrp($cache, $user[1]) === false) {
                    rmdir($cache);
                    return error(sprintf(CHGRP_NOT_SET, $user[1], $cache));
                }
            }
        }
        info($inline ? "SKIPPED" : "  Initializing minify...DONE");

    }// initMinify


    private function is($param,$value) {
        if(is_bool($param)) {
            return $param;
        } elseif(is_string($param)) {
            if(strcasecmp($param,'true') === 0 || strcasecmp($param,'1') === 0) {
                return true;
            } elseif(strcasecmp($param,'false') === 0 || strcasecmp($param,'0') === 0) {
                return false;
            }
        } elseif(is_array($param)) {
            return isset($param[$value]);
        }
        return $param === $value;
    }


}// Install
