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
     * Installation parameters
     * @var array
     */
    private $params;


    /**
     * Use defaults if TRUE, prompt otherwise.
     * @var boolean
     */
    private $silent;


    /**
     * Update libraries
     * @var boolean
     */
    private $update;


    /**
     * Constructor
     *
     * @param string $root Installation root
     * @param array $params Installation parameters
     * @param boolean $silent Use defaults if TRUE, prompt otherwise.
     * @param boolean $init Initialize modules (long operation).
     * @param boolean $update Update libraries if installed.
     *
     *
     * @since 19. June 2013
     *
     */
    public function __construct($root, $params, $silent, $init, $update)
    {
        $this->root = $root;
        $this->params = $params;
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
        // In dev-mode?
        if (in_phar() === false) {
            $this->initComposer();
            $this->initLibs();
        }

        $this->initConfig();

        $this->initMinify();

        $this->initModules();

        // Create RescueMe VERSION file
        if (file_put_contents($this->root . DIRECTORY_SEPARATOR . "VERSION", $this->params[PARAM_VERSION]) === FALSE) {
            return error(sprintf(VERSION_NOT_SET, $this->params[PARAM_VERSION]));
        }

        // Finished
        return true;


    }// execute


    /**
     * Initialize config from ini and write to files
     * @return bool|string
     */
    public function initConfig()
    {
        info("  Initializing config...");

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
            'SALT' => str_escape($this->params['SALT']),
            'TITLE' => str_escape($this->params['TITLE']),
            'DB_HOST' => str_escape($this->params['DB_HOST']),
            'DB_NAME' => str_escape($this->params['DB_NAME']),
            'DB_USERNAME' => str_escape($this->params['DB_USERNAME']),
            'DB_PASSWORD' => str_escape($this->params['DB_PASSWORD']),
            'COUNTRY_PREFIX' => str_escape($this->params['COUNTRY_PREFIX']),
            'DEFAULT_LOCALE' => str_escape($this->params['DEFAULT_LOCALE']),
            'DEFAULT_TIMEZONE' => str_escape($this->params['DEFAULT_TIMEZONE']),
            'GOOGLE_MAPS_API_KEY' => str_escape($this->params['GOOGLE_MAPS_API_KEY']),
            'GOOGLE_GEOCODING_API_KEY' => str_escape($this->params['GOOGLE_GEOCODING_API_KEY']),
            'DEBUG' => $this->params['DEBUG'],
            'MAINTAIN' => $this->params['MAINTAIN']
        ));

        // Create config.php
        $path = implode(DIRECTORY_SEPARATOR, array($this->root,"config.php"));
        if (file_put_contents($path, $config) === FALSE) {
            return error(CONFIG_NOT_CREATED);
        }
        info(sprintf("    Initialized [%s]",$path));

        // Get config minify template
        $config_minify = replace_define_array($config_minify, array
        (
            'MINIFY_MAXAGE' => $this->params['MINIFY_MAXAGE']
        ));

        // Create config.minify.php
        $path = implode(DIRECTORY_SEPARATOR, array($this->root,"config.minify.php"));
        if (file_put_contents($path, $config_minify) === FALSE) {
            return error(CONFIG_MINIFY_NOT_CREATED);
        }
        info(sprintf("    Initialized [%s]",$path));

        // Create apache logs folder
        $path = realpath($this->root . DIRECTORY_SEPARATOR . "logs");
        if (!file_exists($path)) {
            mkdir($this->root . DIRECTORY_SEPARATOR . "logs");
            info(sprintf("    Initialized [%s]",$path));
        }

        // Define minimum constants required by install script (loading config files is not necessary)
        if(!defined('SALT')) define('SALT', $config['SALT']);
        if(!defined('DB_NAME')) define('DB_NAME', $config['DB_NAME']);
        if(!defined('DB_HOST')) define('DB_HOST', $config['DB_HOST']);
        if(!defined('DB_USERNAME')) define('DB_USERNAME', $config['DB_USERNAME']);
        if(!defined('DB_PASSWORD')) define('DB_PASSWORD', $config['DB_PASSWORD']);

        info("  Initializing config...DONE");

        return true;

    }// initConfig


    private function initComposer() {

        $inline = true;
        info("  Installing composer....", BUILD_INFO, NEWLINE_NONE);

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
    }// initComposer


    private function initLibs(){

        info("  Initializing libraries...");

        $vendor = $this->root . DIRECTORY_SEPARATOR . "vendor";

        if(realpath($vendor)) {

            info("     Update dependencies...", BUILD_INFO, NEWLINE_NONE);

            if ($this->update) {

                $composer = $this->root . DIRECTORY_SEPARATOR . "composer.phar";

                $cmd = "php $composer update --no-scripts --working-dir='$this->root'";
                exec($cmd, $output, $retval);
                if ($retval !== 0) {
                    $output = implode("\n", $output);
                    return error("     Failed to update libraries: \n$output\n");
                }

                info("DONE");

            } else {

                info("SKIPPED");
            }

        } else {

            info("     Install dependencies...", BUILD_INFO, NEWLINE_NONE);

            $composer = $this->root . DIRECTORY_SEPARATOR . "composer.phar";

            $cmd = "php $composer install --no-scripts --working-dir='$this->root'";
            exec($cmd, $output, $retval);
            if ($retval !== 0) {
                $output = implode("\n", $output);
                return error("     Failed to install libraries: \n$output\n");
            }

            info("DONE");
        }

        info("  Initializing libraries....DONE");

    }// initLabs

    private function initMinify() {

        $inline = true;
        info("  Initializing minify...");

        $cache = implode(DIRECTORY_SEPARATOR, array($this->root, "min", "cache"));
        info(sprintf("    Cache path is [%s]", $cache));
        if (!file_exists($cache)) {

            if (mkdir($cache) === false) {
                return error(sprintf(DIR_NOT_CREATED, $cache));
            }
            if(is_linux()) {
                if (!is_sudo()) {
                    rmdir($cache);
                    return error(NOT_SUDO);
                }
                $user = "www-data:www-data";
                if ($this->silent === false) {
                    $user = in("    Webservice username", $user);
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
            info(sprintf("    Configured minify cache [%s]",$cache));
        } else {
            info(sprintf("    Minify cache [%s] is configured",$cache));
        }
        info("  Initializing minify..." . ($inline ? "SKIPPED" : "DONE"));

    }// initMinify

    private function initModules() {
        $inline = true;
        info("  Initializing modules....", BUILD_INFO);

        $callback = function($progress) use ($inline) {
            info("    $progress", BUILD_INFO);
        };

        if (Manager::install($this->init, $callback) !== false) {
            info("    >> System modules installed", BUILD_INFO);
            $inline = false;
        }

        // Prepare user modules
        $users = User::getAll();
        if ($users !== false) {
            /** @var User $user */
            foreach ($users as $user) {
                if (Manager::prepare($user->id)) {
                    info("    >> Modules for [$user->name] installed", BUILD_INFO, $inline ? NEWLINE_BOTH : NEWLINE_POST);
                    $inline = false;
                }
            }
        }
        info($inline ? "SKIPPED" : "  Initializing modules....DONE");

    }


}// Install
