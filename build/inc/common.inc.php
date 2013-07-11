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
    define('PRE', -1);
    define('NONE', 0);
    define('POST', 1);
    define('BOTH', 2);
    define('ERROR', -1);
    define('SUCCESS', 0);
    define('INFO', 'INFO');
    define('DONE', "DONE");
    define('FAILED', "FAILED");
    define('NOT_FOUND', "not found");
    define('RM_DIR_FAILED', "remove directory failed");
    define('DIR_EXISTS', "directory exists");
    define('MAKE_DIR_FAILED', "make directory failed");
    define('ZIP_OPEN_FAILED', "zip could not be opened");
    define('ZIP_COPY_FAILED', "zip could not be copied");
    define('DB_NOT_CREATED', "%s could not be created");
    define('DB_NOT_IMPORTED', "%s could not be imported");
    define('CONFIG_NOT_CREATED', "config.php could not be created");
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
     * @param mixed $remove Remove parent subpath if found.
     */
    function add_folder_to_zip($dir, $zipArchive, $remove=NULL)
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
                        
                    // If it's a folder, run the function again!
                    if(!is_file($filename))
                    {
                        // Skip parent and root directories
                        if(($file !== ".") && ($file !== ".."))
                        {
                            add_folder_to_zip($filename . "/", $zipArchive, $remove);
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
        return preg_match("#define\('$name',(.*)\)#", $subject, &$values) === 1 ? $values[1] : $default;
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
     * Initialize constant value in subject.
     * 
     * @param string $subject Replace substring in subject
     * @param array $name Constant name array
     * 
     * @return string
     */
    function ini_define($subject, $names) {
        foreach($names as $name) $subject = replace_define($subject, $name, "'$name'");
        return $subject;
    }// ini_define
    

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
        return preg_replace("#define\('$name',.*\)#", "define('$name',$value)", $subject);
    }// replace_define
    
    
    /**
     * Replace constant value in subject.
     * 
     * @param string $subject Replace substring in subject
     * @param mixed $name Constant name
     * @param string $value Constant value
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
     * Get random string of given length
     * @param integer $length String lengt
     * @return string
     */
    function str_rnd($length = 15)
    {
        return substr(sha1(rand()), 0, $length);
    }// str_rnd
    
    /**
     * Escape string with "'"
     * 
     * @param string $string
     * @param string $delimiter"
     */
    function str_escape($value) {
        return isset($value) ? ("'".trim($value,"'")."'") : "''";
    }// str_escape
    
    
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
                    if(filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
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
     * @param integer $newline Message newline [optional, default: NONE]
     * 
     * @return string Answer 
     */
    function in($message, $default=NULL, $newline=NONE) {
        out(($default ? "$message [$default]" : $message).": ", $newline, COLOR_INFO);
        $answer = fgets(STDIN);
        return ($answer !== PHP_EOL ? str_replace("\n", "", $answer) : $default);
    }// in
    
    
    /**
     * Get option argument value
     * 
     * @param array $opts Option array
     * @param string $arg Argument name
     * @param mixed $default Default value
     * @return mixed
     */
    function get($opts, $arg, $default = NULL, $escape = true)
    {
        $value = isset($opts[$arg]) ? $opts[$arg] : $default;
        return $escape ? str_escape($value) : trim($value,"'");
    }// get


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
     * Log action begun
     * 
     * @param string $action Action name
     * 
     * @return void
     */
    function begin($action)
    {
        info("$action...", SUCCESS, NONE);
    }// done


    /**
     * Log action done and exit script with given status
     * 
     * @param string $action Action name
     * @param integer $status Action status
     * @param integer $newline Message newline [optional, default: BOTH]
     * 
     * @return void
     */
    function done($action, $status = SUCCESS, $newline = BOTH)
    {
        if($status === ERROR) {
            error("$action...".FAILED.PHP_EOL, $status, $newline);
        } 
        else {
            info("$action...".DONE.PHP_EOL, $status, $newline);
        }
        exit($status);
    }// done


    /**
     * Log fatal error.
     * 
     * This method forces an exit with given status code
     * 
     * @param string $message Message
     * @param integer $status Exit status [optional, default: 0]
     * @param integer $newline Message newline [optional, default: POST]
     * 
     * @since 02. October 2012 
     * 
     * @return void
     */
    function fatal($message, $status = ERROR, $newline=POST)
    {
        // Log event, cleanup and terminate
        exit (error($message, $status, $newline));
    }// fatal


    /**
     * Log information
     * 
     * @param string $message Message
     * @param integer $status Status
     * @param integer $newline Message newline [optional, default: POST]
     * 
     * @since 02. October 2012 
     * 
     * @return integer
     * 
     */
    function info($message, $status = SUCCESS, $newline=POST)
    {
        out($message,$newline,COLOR_INFO); return $status;
    }// info


    /**
     * Log error
     * 
     * @param string $message Message
     * @param integer $status Status
     * @param integer $newline Message newline [optional, default: POST]
     * 
     * @since 02. October 2012 
     * 
     * @return integer
     * 
     */
    function error($message, $status = ERROR, $newline=POST)
    {
        out($message,$newline,COLOR_ERROR); return $status;
    }// error


    /**
     * Output message
     * 
     * Adapted from https://github.com/composer/getcomposer.org/blob/master/web/installer
     * 
     * @param string $message Message
     * @param integer $newline Message newline [optional, default: POST]
     * @param string $color Output color
     * 
     * @since 07. June 2013
     * 
     * @return void
     * 
     */
    function out($message, $newline=POST, $color = COLOR_NONE)
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
            case PRE:
                printf($format, PHP_EOL.$message);
                break;
            case POST:
                printf($format, $message.PHP_EOL);
                break;
            case BOTH:
                printf($format, PHP_EOL.$message.PHP_EOL);
                break;
            case NONE:
            default:
                printf($format, $message);
                break;
        }
    }// out
    
    
    /**
     * Get debug information.
     * 
     * @param string $name Name
     * @return string
     */
    function toDebug($name)
    {
        return "'" . $name . "' [" . dechex(\cim\common\crc32($name)) . "]";
    }// toDebug
    