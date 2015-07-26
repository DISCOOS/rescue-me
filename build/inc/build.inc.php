<?php

    /**
	 * RescueMe Build Script Common Functions
	 * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
    
    // Define common constants
    define('NEWLINE_PRE', -1);
    define('NEWLINE_NONE', 0);
    define('NEWLINE_POST', 1);
    define('NEWLINE_BOTH', 2);
    define('BUILD_ERROR', -1);
    define('BUILD_SUCCESS', 0);
    define('BUILD_INFO', 1);
    define('BUILD_CANCEL', 2);
    define('FAILED', "FAILED");
    define('CANCELLED', "CANCELLED");
    define('RM_DIR_FAILED', "remove directory failed");
    define('DIR_EXISTS', "directory exists");
    define('MAKE_DIR_FAILED', "make directory failed");
    define('ZIP_OPEN_FAILED', "zip could not be opened");
    define('ZIP_COPY_FAILED', "zip could not be copied");
    define('DB_NOT_CREATED', "%s could not be created");
    define('DB_NOT_IMPORTED', "%s could not be imported");
    define('VERSION_NOT_SET', "Version %s could not be set");    
    define('ADMIN_NOT_CREATED', "Admin user not created");    
    define('SQL_NOT_IMPORTED', 'SQL not imported');
    define('SQL_NOT_EXPORTED', 'SQL not exported');
    define('CONFIG_NOT_CREATED', "config.php could not be created");
    define('CONFIG_MINIFY_NOT_CREATED', "config.minify.php could not be created");
    define('DIR_NOT_CREATED', "Failed to create directory %s");
    define('CHOWN_NOT_SET', "Failed to set %s as owner of %s");
    define('CHGRP_NOT_SET', "Failed to set %s as group of %s");
    define('NOT_SUDO', "Please execute with sudo");
    define('COLOR_NONE', 'none');
    define('COLOR_INFO', 'info');
    define('COLOR_ERROR', 'error');
    define('COLOR_SUCCESS', 'success');


    /**
     * Parses parameters into an array.
     *
     * Supports:
     * -e
     * -e <value>
     * --long-param
     * --long-param=<value>
     * --long-param <value>
     * <value>
     *
     * @param array $params List of parameters
     * @param array $noopt List of parameters without values
     *
     * @return array
     */
    function parse_opts($params, $noopt = array()) {
        
        $result = array();
        
        // Could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
        reset($params);
        while(list($tmp, $p) = each($params)) {
            
            if($p{0} == '-') {
                
                $pname = substr($p, 1);
                $value = true;
                if($pname{0} == '-') {
                    
                    // Long option? (--<param>)
                    $pname = substr($pname, 1);
                    if(strpos($p, '=') !== false){
                        // Value specified inline (--<param>=<value>)
                        list($pname, $value) = explode('=', substr($p, 2), 2);
                    }
                }
                // Check if next parameter is a descriptor or a value
                $nextparm = current($params);
                if(!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-') {
                    list($tmp, $value) = each($params);
                }
                $result[$pname] = $value;
            } 
            else {
                // Param doesn't belong to any option
                $result[] = $p;
            }
        }

        // Set action
        $result[ACTION] = isset($result[1]) ? $result[1] : null;
        $result[NAME] = isset($result[2]) ? $result[2] : null;

        // Finished
        return $result;
        
    }// parse_opts


    /**
     * Function to recursively add a directory, sub-directories and files to a zip archive
     * 
     * @param string $dir
     * @param ZipArchive $zipArchive
     * @param string $remove Remove parent subpath if found.
     * @param string $exclude Exclude paths matching this pattern
     */
    function add_folder_to_zip($dir, $zipArchive, $remove=NULL, $exclude=NULL)
    {
        if(is_dir($dir))
        {
            if(($dh = opendir($dir)) !== FALSE)
            {
                // Loop through all the files
                while(($file = readdir($dh)) !== false)
                {
                    // Get filename
                    $filename = $dir . $file;
                    
                    // Include?
                    if($exclude === null || preg_match("#$exclude#", $filename) !== 1) {
                        
                        $match = preg_match("#$exclude#", $filename);
                        
                        // If it's a folder, run the function again!
                        if(!is_file($filename))
                        {
                            // Skip parent and root directories
                            if(($file !== ".") && ($file !== ".."))
                            {
                                add_folder_to_zip($filename . DIRECTORY_SEPARATOR, $zipArchive, $remove, $exclude);
                            }
                        }
                        else
                        {
                            // Get local name
                            $local = ($remove ? str_freplace($remove, '', $filename) : $filename);

                            // Add file    
                            $zipArchive->addFile($dir . $file, $local);

                            // TODO: Add info($local) on verbose output;
                        }// else
                    }// if
                }// while
            }// if
        }// if
    }// add_folder_to_zip


    /**
     * Replace first occurence in string
     * 
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string Result after replacement
     */
    function str_freplace($search, $replace, $subject)
    {
        return preg_replace("#$search#", $replace, $subject, 1);
    }// preg_freplace
        
    
    /**
     * Replace last occurence in string
     * 
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string Result after replacement
     */
    function str_lreplace($search, $replace, $subject)
    {
        return preg_replace('#~(.*)#' . preg_quote($search, '~') . '~', '$1' . $replace, $subject, 1);
    }// preg_ireplace
        
    
    /**
     * Get constant value from subject.
     * 
     * @param string $subject Replace substring in subject
     * @param string $name Constant name
     * @param string $default Default value if constant not found
     * 
     * @return string
     */
    function get_define($subject, $name, $default='') {
        $values = array();
        return trim(trim(preg_match("#define\('$name',(.*)\)#", $subject, $values) === 1 ? $values[1] : $default),"'\"");
    }// replace_define
    
    
    /**
     * Get constant value array from subject.
     * 
     * @param string $subject Replace substring in subject
     * @param string $name Constant name
     * @param string $default Default value if constant not found
     * 
     * @return string
     */
    function get_define_array($subject, $names) {
        $values = array();
        foreach($names as $name) {
            $values[$name] = get_define($subject, $name);
        }
        return $values;
    }// replace_define_array
    
    
    /**
     * Replace constant value in subject.
     * 
     * @param string $subject Replace substring in subject
     * @param mixed $name Constant name
     * @param string $value Constant value
     * 
     * @return string
     */
    function replace_define($subject, $name, $value) {
        if(!isset($value) || empty($value) || is_null($value)) {
            $value = 0;
        }
        return trim(trim(preg_replace("#define\\('$name',.*\\)#", "define('$name',$value)", $subject)),"'\"");
    }// replace_define


    /**
     * Replace constant value in subject.
     *
     * @param string $subject Replace substring in subject
     * @param $contants
     * @internal param mixed $name Constant name
     * @internal param string $value Constant value
     *
     * @return string
     */
    function replace_define_array($subject, $contants) {
        foreach($contants as $name => $value) {
            $subject = replace_define($subject, $name, $value);
        }
        return $subject;
    }// replace_define_array
    
    
    /** 
     * Recursively delete directory.
     * 
     * @param string $dir
     * @param boolean $safe Root is preserved when TRUE
     * 
     * @return boolean TRUE if success, FALSE otherwise.
     */
    function rrmdir($dir, $safe=true)
    {
        // Preserve root?
        if($safe && (!isset($dir) || empty($dir) || $dir === DIRECTORY_SEPARATOR)) {
            return false;
        }// if
        
        if(is_dir($dir))
        {
            $objects = scandir($dir);
            foreach($objects as $object)
            {
                if($object != "." && $object != "..")
                {
                    if(filetype($dir . DIRECTORY_SEPARATOR . $object) == "dir")
                        rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . '/' . $object);
                }
            }
            reset($objects);
            rmdir($dir);
            return true;
        }
    }// rrmdir


/**
 * Prompt user for input value
 *
 * @param string $message Message
 * @param string $default Default value
 * @param int $newline Message newline [optional, default: NEWLINE_NONE]
 * @param boolean $required Required value.
 * @param boolean $echo Echo entered value.
 * @param array $allowed Allowed answers
 * @param boolean $mapped Allowed answers are mapped to values
 * @return string Answer
 */
    function in($message, $default=NULL, $newline=NEWLINE_NONE, $required=true, $echo=true, $allowed=null, $mapped=false) {
        $isset = isset($default) && (!empty($default) || $default == 0 && $default !== '');
        out(($isset ? "$message [$default]" : $message).": ", $newline, COLOR_INFO);
        $answer = fgets(STDIN);
        $answer = ($answer !== PHP_EOL ? str_replace("\n", "", $answer) : "$default");

        if($required && !trim($answer,"'") && trim($answer,"'") !== '0' ||
            is_array($allowed) && !in_array(trim(strtoupper($answer)), $mapped ? array_keys($allowed) : $allowed, true))
        {
            return in($message, $default, $newline, $required, $echo, $allowed, $mapped);
        }
        $answer = trim($answer);

        if($echo) {
            out("$message: $answer", NEWLINE_POST, COLOR_SUCCESS);
        }

        if(is_array($allowed) && $mapped) {
            $answer = $allowed[strtoupper($answer)];
        }

        return $answer;
    }// in


    /**
     * Get option argument value
     *
     * @param array $opts Option array
     * @param string $arg Argument name
     * @param mixed $default Default value
     * @param bool $escape
     * @return mixed
     */
    function get($opts, $arg, $default = NULL, $escape = true)
    {
        // Allow 0 as value, but not empty string
        $isset = isset($opts[$arg]) && (!empty($opts[$arg]) || $opts[$arg] == 0 && $opts[$arg] !== '');
        $value = $isset ?  $opts[$arg] : $default;
        
        return $escape ? str_escape($value) : trim($value,"'\"");
        
    }// get
    
    
    /**
     * Prompt user for timezone.
     * 
     * @param array $opts Option array
     * @param mixed $default Default timezone value
     *
     * @return mixed
     */
    function in_timezone($opts, $default=null) {
        
        $current = date_default_timezone_get();
        
        // Replace default with current?
        if(!isset($default) || empty($default) || trim($default,"'") == ''){
            $default = $current;
        }
        $timezone = get($opts, "DEFAULT_TIMEZONE", $default);
        // Replace given timezone with default?
        if(!isset($timezone) || empty($timezone) || trim($timezone,"'") == '') {            
            $timezone = $default;
        }
        $timezone = in("Default timezone",$timezone, NEWLINE_NONE, true, false);
        $old = error_reporting(E_ALL ^ E_NOTICE);
        $current = date_default_timezone_get();
        if(@date_default_timezone_set(trim($timezone,"'")) === FALSE) {
            out("Invalid timezone: $timezone", NEWLINE_POST, COLOR_SUCCESS);
            return in_timezone($opts, $default);
        }
        error_reporting($old);
        date_default_timezone_set($current);
        out("Timezone: $timezone", NEWLINE_POST, COLOR_SUCCESS);
        return $timezone;
    }


    /**
     * Get tail argument value
     * 
     * @param array $opts Option array
     * @param mixed $default Default value
     * @return mixed
     */
    function tail($opts, $default)
    {
        $end = end($opts);
        return (is_numeric(key($opts))) ? $end : $default;
    }// tail
    
    
    /**
     * Get configuration parameters
     * 
     * @param string $root
     * 
     * @return array
     */
    function get_config_params($root, $keys) {
        if(file_exists(realpath($root).DIRECTORY_SEPARATOR.'config.php')) {
            // Get from current
            $file = $root.DIRECTORY_SEPARATOR.'config.php';
        } else {
            // Get from template
            $file = (in_phar() ? "" : $root.DIRECTORY_SEPARATOR).'config.tpl.php';
        }        
        // Get current configuration
        $config = file_get_contents($file);
        $config = get_define_array($config, $keys);
        return $config;
    }
    
    
    /**
     * Get minify configuration parameters
     * 
     * @param string $root
     * @return array
     */
    function get_config_minify_params($root) {
        if(file_exists(realpath($root).DIRECTORY_SEPARATOR.'config.minify.php')) {
            // Get from current
            $file = $root.DIRECTORY_SEPARATOR.'config.minify.php';
        } else {
            // Get from template
            $file = (in_phar() ? "" : $root.DIRECTORY_SEPARATOR).'config.minify.tpl.php';
        }        
        // Get current configuration
        $config = file_get_contents($file);
        $config = get_define_array($config, array
        (
            'MINIFY_MAXAGE'
        ));        
        return $config;
    }
    
    /**
     * Get database parameters
     * 
     * @param array $opts
     * @param array $config
     */
    function get_db_params($opts, $config, $ensure=false) {

        // Get database parameters
        $db = get($opts, PARAM_DB, isset_get($config, "DB_NAME", ""), false);
        $host = get( $opts, PARAM_HOST, isset_get($config, "DB_HOST", ""), false);
        $username = get($opts, PARAM_USERNAME, isset_get($config, "DB_USERNAME", ""), false);
        $password = get($opts, PARAM_PASSWORD, isset_get($config, "DB_PASSWORD", ""), false);

        // Ensure missing parameters?
        if($ensure) {
            $db = $db ? $db : in("Database Name", "rescueme");
            $host = $host ? $host : in("Database Host", "localhost");
            $username = $username ? $username : in("Database Username");
            $password = $password ? $password : in("Database Password");
        } 
        
        // Trim values
        $opts[PARAM_DB] = trim($db, "'");
        $opts[PARAM_HOST] = trim($host, "'");
        $opts[PARAM_USERNAME] = trim($username, "'");
        $opts[PARAM_PASSWORD] = trim($password, "'");
               
        // Finished
        return $opts;        
    }
    
    
    /**
     * Get safe directory path (remove trailing slashes)
     * 
     * @param array $opts
     * @param string $key
     * @param string $default
     * @return string
     */
    function get_safe_dir($opts, $key, $default) {
        
        // Get path 
        $dir = get($opts, $key, $default, false);

        // Use current working directory?
        if($dir === ".") $dir = getcwd();

        // Remove trailing slashes
        return rtrim($dir,DIRECTORY_SEPARATOR);
        
    }
    
    
    /**
     * Check if script is running in a phar-archive.
     * 
     * @return boolean
     */
    function in_phar() {
        return Phar::running() !== '';
    }
    
    
    /**
     * Log action begun
     * 
     * @param string $action Action name
     * 
     * @return void
     */
    function begin($action)
    {
        info("rescueme [$action]...", BUILD_SUCCESS);
    }// done

    
    /**
     * Log action done. 
     * 
     * @param string $action Action name
     * @param integer $status Action status
     * @param integer $newline Message newline [optional, default: POST]
     * 
     * @return void
     */
    function done($action, $status = BUILD_SUCCESS, $newline=NEWLINE_POST)
    {
        switch($status) { 
            case BUILD_ERROR:
                fatal("rescueme [$action]...".FAILED.PHP_EOL, BUILD_ERROR, $newline);
            case BUILD_CANCEL:
                fatal("rescueme [$action]...".CANCELLED.PHP_EOL, BUILD_CANCEL, $newline);
            default:
                info("rescueme [$action]...DONE".PHP_EOL, $status, $newline);
                break;
        }
    }// done


    /**
     * Log fatal error.
     * 
     * This method forces an exit with given status code
     * 
     * @param string $message Message
     * @param integer $status Exit status [optional, default: 0]
     * @param integer $newline Message newline [optional, default: NEWLINE_POST]
     * 
     * @since 02. October 2012 
     * 
     * @return void
     */
    function fatal($message, $status = BUILD_ERROR, $newline=NEWLINE_POST)
    {
        // Log event, cleanup and terminate
        exit(error($message, $status, $newline));
        
    }// fatal


    /**
     * Log information
     * 
     * @param string $message Message
     * @param integer $status Status
     * @param integer $newline Message newline [optional, default: NEWLINE_POST]
     * 
     * @since 02. October 2012 
     * 
     * @return integer
     * 
     */
    function info($message, $status = BUILD_INFO, $newline=NEWLINE_POST)
    {
        out($message,$newline,$status === BUILD_SUCCESS ? COLOR_SUCCESS : COLOR_INFO); return $status;
    }// info


    /**
     * Log error
     * 
     * @param string $message Message
     * @param integer $status Status
     * @param integer $newline Message newline [optional, default: NEWLINE_POST]
     * 
     * @since 02. October 2012 
     * 
     * @return integer
     * 
     */
    function error($message, $status = BUILD_ERROR, $newline=NEWLINE_POST)
    {
        out($message, $newline, COLOR_ERROR); return $status;
    }// error


    /**
     * Output message
     * 
     * Adapted from https://github.com/composer/getcomposer.org/blob/master/web/installer
     * 
     * @param string $message Message
     * @param integer $newline Message newline [optional, default: NEWLINE_POST]
     * @param string $color Output color
     * 
     * @since 07. June 2013
     * 
     * @return void
     * 
     */
    function out($message, $newline=NEWLINE_POST, $color = COLOR_NONE)
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            $hasColorSupport = false !== getenv('ANSICON');
        } else {
            $hasColorSupport = true;
        }

        $styles = array(
            'success' => "\033[0;32m%s\033[0m",
            'error' => "\033[31;31m%s\033[0m",
            'info' => "\033[33;33m%s\033[0m"
        );

        $format = '%s';

        if (isset($styles[$color]) && $hasColorSupport) {
            $format = $styles[$color];
        }

        switch($newline)
        {
            case NEWLINE_PRE:
                printf($format, PHP_EOL.$message);
                break;
            case NEWLINE_POST:
                printf($format, $message.PHP_EOL);
                break;
            case NEWLINE_BOTH:
                printf($format, PHP_EOL.$message.PHP_EOL);
                break;
            case NEWLINE_NONE:
            default:
                printf($format, $message);
                break;
        }
    }// out

    function is_sudo() { 
        return !is_win() && posix_getuid() === 0;         
    }
    
