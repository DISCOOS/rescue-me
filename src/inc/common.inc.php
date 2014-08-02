<?php
    
    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;    
    use RescueMe\Properties;

    /**
     * Perform system sanity checks
     *
     * @param string $action Action-sensitive check
     *
     * @return array True if successful, array of string otherwise
     */
    function system_checks($action='') {

        $status = array();

        $action = strtolower($action);

        if(ini_get("short_open_tag") !== "1") {
            $status[] = array(E_USER_ERROR, "php.ini value 'short_open_tag' must be '1'");
        }
        if(ini_get("date.timezone") === FALSE) {
            $status[] = array(E_USER_ERROR, "php.ini value 'date.timezone' is not set");
        }
        if(extension_loaded('curl') === false) {
            $message = array();
            $message[] = 'Extension "curl" is not installed correctly';
            if(is_win()) {
                $message[] = 'Uncomment "extension = php_curl.dll" in php.ini';
            } else {
                $message[] = 'Run "sudo apt-get install php5-curl"';
            }
            $status[] = array(E_USER_ERROR, $message);
        }

        if($action === "install" || $action == "configure") {

            if (extension_loaded('suhosin')) {
                if (stristr(ini_get('suhosin.executor.include.whitelist'), 'phar') === false) {
                    $message = array();
                    $message[] = "RescueMe build scripts requires 'phar://' includes to be enabled.";
                    $message[] = "Add 'phar' to 'suhosin.executor.include.whitelist' in 'php.ini'.";
                    $status[] = array(E_USER_ERROR, $message);
                }
                if (stristr(ini_get('suhosin.executor.include.blacklist'), 'phar') !== false
                ) {
                    $message = array();
                    $message[] = "RescueMe build script requires 'phar://' includes to be enabled.";
                    $message[] = "Remove 'phar' from 'suhosin.executor.include.blacklist' in 'php.ini'.";
                    $status[] = array(E_USER_ERROR, $message);
                }
            }
            if(os_command_exists("php") === FALSE) {
                $message = array();
                $message[] = "php-cli is not configured correctly";
                if(is_win()) {
                    $message[] = 'Run php installer again and select "Script Executable"';
                } else {
                    $message[] = 'Run "sudo apt-get install php5-cli"';
                }
                $status[] = array(E_USER_ERROR, $message);
            }
            if(extension_loaded("intl") === false) {
                $message = array();
                $message[] = "Extension 'intl' should be enabled for better locale handling.";
                if(is_win()) {
                    $message[] = 'Uncomment "extension = php_intl.dll" in php.ini';
                } else {
                    $message[] = 'Run "sudo apt-get install php5-intl"';
                }
                $status[] = array(E_USER_WARNING, $message);
            }
            if(extension_loaded("gettext") === false) {
                $message = array();
                $message[] = 'Extension "gettext" should be enabled for better locale support.';
                if(is_win()) {
                    $message[] = 'Uncomment "extension = php_gettext.dll" in php.ini';
                } else {
                    $message[] = 'Run "sudo apt-get install php5-gettext"';
                }
                $status[] = array(E_USER_WARNING, $message);
            }
        }

        return empty($status) ? true : $status;
    }

    /**
     * Download given url to given file
     * @param string $url
     * @param string $file
     * @return boolean
     */
    function download($url, $file) {

        // File to save the contents to
        $fp = fopen($file, 'w+');

        // Replace spaces with %20
        $ch = curl_init(str_replace(" ","%20", $url));

        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    /**
     * Check if command exists on host OS.
     * @param string $command
     * @return boolean
     */
    function os_command_exists($command)
    {
        $whereIsCommand = is_win() ? 'where' : 'which';

            $pipes = array();
        $process = proc_open(
            "$whereIsCommand $command", array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ), $pipes
        );
        if($process !== false)
        {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $stdout != '';
        }

        return false;
    }

    function is_osx() {
        $uname = strtolower(php_uname());
        return (strpos($uname, "darwin") !== false);
    }


    function is_linux() {
        $uname = strtolower(php_uname());
        return (strpos($uname, "linux") !== false);
    }


    function is_win() {
        $uname = strtolower(php_uname());
        return (strpos($uname, "win") !== false) && !is_osx();
    }

    function dec_to_dms($dec)
    {
        // Converts decimal longitude / latitude to DMS
        // ( Degrees / minutes / seconds ) 
        // This is the piece of code which may appear to 
        // be inefficient, but to avoid issues with floating
        // point math we extract the integer part and the float
        // part by using a string function.

        $vars = explode(".", $dec);
        $deg = $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = (float)$tempma * 3600;
        $min = floor($tempma / 60);
        $sec = round($tempma - ($min * 60));
        $des = $tempma - ($min * 60) - $sec;
        $des = explode('.', $des);
        $des = isset($des[1]) ? (int)$des[1] : 0;

        return array("deg" => $deg, "min" => $min, "sec" => $sec, "des" => $des);
    }
    
    
    function is_ajax_request() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }        
    
    
    /**
     * Converts ajax request into response.
     * 
     * Returns json string with structure {html: 'string', options: 'array'}
     * 
     * @param string $resource Resource name
     * @param string $index Resource index
     * @param array $context Resouce context
     * @return string
     */
    function ajax_response($resource, $index = '', $context = '') {
        
        if($index) {
            $index = '.'.$index;
        }
        
        return require "ajax/$resource$index.ajax.php";
        
    }
    
    function create_paginator($current, $total, $user_id) {
        
        $options['size'] =  'normal';
        $options['alignment'] = 'center';
        $options['currentPage'] = $current;
        $options['totalPages'] = $total;
        
        return $options;
    }
    
    
    function create_ajax_response($html, $options = array()) {
        
        $response = array();
        $response['html'] = $html;
        $response['options'] = $options;

        return json_encode($response);
        
    }
    
    
    function input_get_int($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_get_email($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }
    
    function input_get_ip($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }
    
    function input_get_url($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }
    
    function input_get_float($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }
    
    function input_get_boolean($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }
    
    function input_get_hash($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_get_string($key, $default = false) {
        $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_int($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_email($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_ip($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_url($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_float($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_boolean($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_hash($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }

    function input_post_string($key, $default = false) {
        $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
        return $value === false ? $default : $value;
    }
    
    function assert_types($values)
    {
        foreach($values as $type => $value)
        {
            assert_type($type, $value);
        }
    }
    
    function assert_type($expected, $actual)
    {
        if(call_user_func("is_$expected", $actual) === FALSE)
        {
            throw new Exception("[$actual] is not of type '$actual'.");
        }
    }
    
    function assert_isset_all($values, $keys, $message = '', $log = Logs::SYSTEM, $level = LogLevel::ERROR) {
        $keys = is_array($keys) ? $keys : array($keys);
        $missing = array();
        foreach($keys as $key) {
            if(isset($values[$key]) === FALSE) {
                $missing[] = $key;
            }
        }
        $valid = empty($missing);
        if($valid === FALSE) {
            if($message) {
                $message .= ". ";
            }
            Logs::write($log, $level, $message. sprintf(MISSING_VALUES_S, implode(", ", $missing)));
        }
        return $valid;
    }
    

    function prepare_values($fields, $values) 
    {
        reset($fields);
        reset($values);
        $prepared = array();
        foreach($fields as $field) {
            $value = current($values);
            if($value === FALSE) break;
            $prepared[$field] = $value;
            next($values);
        }
        return $prepared;
    }    
    
    function isset_get($array, $key, $default=null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
    
    function array_pick($array, $key) {
        $values = array();
        foreach($array as $name => $value) {
            if($key === $name 
                || is_string($key) && strstr($name, $key) !== false
                || is_array($key) && in_array($name, $key)) {
                $values[$name] = $value;
            }
        }
        return $values;
    }
    
    /**
     * Exclude given key(s) from array
     * 
     * @param array $array
     * @param mixed $key
     * @return array
     */
    function array_exclude($array, $key) {
        $values = array();
        foreach($array as $name => $value) {
            if(!($key === $name 
                || is_string($key) && strstr($name, $key) !== false
                || is_array($key) && in_array($name, $key))) {
                $values[$name] = $value;
            }
        }
        return $values;
    }
    
    
    function is_function($value) {
        return preg_match("#^([A-Za-z0-9_]+)\(.*\)$#", $value) !== 0;
    }
    
    /** 
     * Get random string of given length
     * 
     * @param integer $length String lengt
     * @return string
     */
    function str_rnd($length = 8)
    {
        $str = '';
        
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&_+?';
        
        $max = (strlen($chars) - 1);

        for($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $max)];
        }

        return $str;
    }// str_rnd
    
    
    /**
     * Escape string with "'"
     * 
     * @param string $value
     * 
     */
    function str_escape($value) {
        return isset($value) ? ("'".trim($value,"'")."'") : "";
    }// str_escape
    
    
    /**
     * Get formatted timestamp
     * 
     * @param string $timestamp mysql timestamp
     */
    function format_dt($timestamp) {        
        $time = strtotime($timestamp);
        return date(date('Y', $time) === date('Y') ? 'd.m H:i' : $timestamp, strtotime($timestamp));
    }
    
    /**
     * Get formatted timestamp
     * 
     * @param string $timestamp mysql timestamp
     * @param boolean $seconds Show seconds
     */
    function format_dtg($timestamp, $seconds=false) {
        return date(($seconds ? 'd-H:i:s' : 'd-Hi'), strtotime($timestamp));
    }
    
    /**
     * Get formatted elapsed time
     * 
     * @param string $timestamp mysql timestamp
     */
    function format_since($timestamp) {
        if(isset($timestamp)) {
            $ts = (int)(time() - strtotime($timestamp));
            $since = "~".UNIT_SECOND;
            if($ts > 0) {
                if($ts < 60) {
                    $since = "$ts ".UNIT_SECOND;
                }
                else if($ts < 2*60*60) {
                    $since = (int)($ts/60)." ".UNIT_MINUTE;
                }
                else {
                    $since = format_dt($timestamp);
                }
            }        
        } else {
            $since = UNKNOWN;
        }
        return $since;
    }
    
    
    function mysql_dt($time) {
        return date( 'Y-m-d H:i:s', $time );
    }


    /**
     * Get formatted position
     *
     * @param null|RescueMe\Position $p Position instance
     * @param string|array $params Format parameters.
     * @param string|boolean $label Set true or label attributes to return label, false otherwise.
     *
     * @return string
     */
    function format_pos($p, $params = array(), $label = true) {

        if(isset($p) === false) {
            $success = false;
            $position = UNKNOWN;
        } else {
            $success = true;
            $type = isset_get($params, Properties::MAP_DEFAULT_FORMAT, Properties::MAP_DEFAULT_FORMAT_UTM);
            $axis = isset_get($params, Properties::MAP_FORMAT_AXIS, Properties::YES) === Properties::YES;
            $unit = isset_get($params, Properties::MAP_FORMAT_UNIT, Properties::YES) === Properties::YES;

            switch($type) {
                default:
                case Properties::MAP_DEFAULT_FORMAT_UTM:
                    $gPoint = new gPoint();
                    $gPoint->setLongLat($p->lon, $p->lat);
                    $gPoint->convertLLtoTM();
                    $format = $axis ? '%1$s E%2$07d N%3$07d' : '%1$s %2$07d %3$07d';
                    $position = sprintf($format,
                        $gPoint->Z(),
                        floor($gPoint->E()),
                        floor($gPoint->N())
                    );
                    break;
                case Properties::MAP_DEFAULT_FORMAT_6D:

                    $gPoint = new gPoint();
                    $gPoint->setLongLat($p->lon, $p->lat);
                    $gPoint->convertLLtoTM();

                    $format = '%1$07d';
                    $e = sprintf($format,floor($gPoint->E()));
                    $e = substr($e,2);
                    $e = round((float)$e / 100);
                    $n = sprintf($format,floor($gPoint->N()));
                    $n = substr($n,2);
                    $n = round((float)$n / 100);

                    $format = $axis ? 'E%1$03d N%2$03d' : '%1$03d %2$03d';
                    $position = sprintf($format,
                        $e,
                        $n
                    );
                    break;
                case Properties::MAP_DEFAULT_FORMAT_DD:
                    // 4 decimal places gives accuracy of ~ 10 m.

                    $lat = floatval($p->lat);
                    $wrap = $axis && (abs($lat) !== $lat);
                    $n = $wrap ? 'S' : 'N';

                    $lon = floatval($p->lon);
                    $wrap = $axis && (abs($lon) !== $lon);
                    $e = $wrap ? 'W' : 'E';

                    $format = $axis ? '%1.4f°' : '%1.4f';
                    $lat = sprintf($format, abs($lat));
                    $lon = sprintf($format, abs($lon));

                    $format = $axis ? $n.'%1$s '.$e.'%2$s' : '%1$s %2$s';
                    $position = sprintf($format, $lat, $lon);

                    break;
                case Properties::MAP_DEFAULT_FORMAT_DEM:

                    $lat = floatval($p->lat);
                    $wrap = $axis && (abs($lat) !== $lat);
                    $n = $wrap ? 'S' : 'N';
                    $lat = dec_to_dem(abs($lat));

                    $lon = floatval($p->lon);
                    $wrap = $axis && (abs($lon) !== $lon);
                    $e = $wrap ? 'W' : 'E';
                    $lon = dec_to_dem(abs($lon));

                    $format = $unit ? "%1$02d° %2$2d.%3$.3s'" : '%1$02d %2$2d.%3$.3s';
                    $lat = sprintf($format,
                        $lat['deg'],
                        $lat['min'],
                        (string)$lat['des']);

                    $lon = sprintf($format,
                        $lon['deg'],
                        $lon['min'],
                        (string)$lon['des']);

                    $format = $axis ? $n.'%1$s '.$e.'%2$s' : '%1$s %2$s';
                    $position = sprintf($format,
                        $lat,
                        $lon
                    );
                    break;

                case Properties::MAP_DEFAULT_FORMAT_DMS:

                    $lat = floatval($p->lat);
                    $wrap = $axis && (abs($lat) !== $lat);
                    $n = $wrap ? 'S' : 'N';
                    $lat = dec_to_dem(abs($lat));

                    $lon = floatval($p->lon);
                    $wrap = $axis && (abs($lon) !== $lon);
                    $e = $wrap ? 'W' : 'E';
                    $lon = dec_to_dem(abs($lon));

                    $format = $unit ? "%1$02d° %2$02d' %3$02.0f''" : '%1$02d %2$02d %3$02.0f';
                    $lat = sprintf($format,
                        $lat['deg'],
                        $lat['min'],
                        $lat['sec']);

                    $lon = sprintf($format,
                        $lon['deg'],
                        $lon['min'],
                        $lon['sec']);

                    $format = $axis ? $n.'%1$s '.$e.'%2$s' : '%1$s %2$s';
                    $position = sprintf($format,
                        $lat,
                        $lon
                    );
                    break;
            }
        }

        if($label !== false) {
            $type = $success ? 'label-success' : 'label-warning';
            $attributes = is_string($label) ? $label : '';
            $position = '<span class="label ' . $type . ' label-position" ' . $attributes. '>'. $position. '</span>';
        }

        return $position;

    }



    
    
    function get_client_ip() {
        
        $client  = getenv("HTTP_CLIENT_IP");
        $forward = getenv("HTTP_X_FORWARDED_FOR");
        $remote  = getenv("REMOTE_ADDR");
        
        if(filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client; 
        } else if(filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward; 
        } else if($remote) {
            $ip = gethostbyname($remote);
        } else {
            $ip = "UNKNOWN";
        }
        return $ip; 
        
    }    
    
    
    /**
     * Encryp string data (sha256)
     * @param mixed $data
     * @param string $secret 
     * @return string
     */
    function encrypt($data, $secret){
        
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $secret = pack('H*', $secret);
        $mac = hash_hmac('sha256', $data, substr(bin2hex($secret), -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secret, $data.$mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
        return $encoded;
    }
    
    
    /**
     * Decrypt data (sha256)
     * @param mixed $data
     * @param string $secret Encryption key
     * @return boolean
     */
    function decrypt($data, $secret){
        $data = explode('|', $data);
        $decoded = base64_decode($data[0]);
        $iv = base64_decode($data[1]);
        $secret = pack('H*', $secret);
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secret, $decoded, MCRYPT_MODE_CBC, $iv));
        $mac = substr($decrypted, -64);
        $decrypted = substr($decrypted, 0, -64);
        $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($secret), -32));
        if($calcmac!==$mac){ return false; }
        $decrypted = unserialize($decrypted);
        return $decrypted;
    }    
    
    function encrypt_id($in) {
        return crypt_id($in, false, 3);
    }
    
    
    function decrypt_id($in) {
        return crypt_id($in, true, 3);
    }
    
    /**
     * Translates a number to a short alhanumeric version
     *
     * Translated any number up to 9007199254740992
     * to a shorter version in letters e.g.:
     * 9007199254740989 --> PpQXn7COf
     *
     * specifiying the second argument true, it will
     * translate back e.g.:
     * PpQXn7COf --> 9007199254740989
     *
     * this function is based on any2dec && dec2any by
     * fragmer[at]mail[dot]ru
     * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
     *
     * If you want the alphaID to be at least 3 letter long, use the
     * $pad_up = 3 argument
     *
     * In most cases this is better than totally random ID generators
     * because this can easily avoid duplicate ID's.
     * For example if you correlate the alpha ID to an auto incrementing ID
     * in your database, you're done.
     *
     * The reverse is done because it makes it slightly more cryptic,
     * but it also makes it easier to spread lots of IDs in different
     * directories on your filesystem. Example:
     * $part1 = substr($alpha_id,0,1);
     * $part2 = substr($alpha_id,1,1);
     * $part3 = substr($alpha_id,2,strlen($alpha_id));
     * $destindir = "/".$part1."/".$part2."/".$part3;
     * // by reversing, directories are more evenly spread out. The
     * // first 26 directories already occupy 26 main levels
     *
     * more info on limitation:
     * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
     *
     * if you really need this for bigger numbers you probably have to look
     * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
     * or: http://theserverpages.com/php/manual/en/ref.gmp.php
     * but I haven't really dugg into this. If you have more info on those
     * matters feel free to leave a comment.
     *
     * The following code block can be utilized by PEAR's Testing_DocTest
     * <code>
     * // Input //
     * $number_in = 2188847690240;
     * $alpha_in  = "SpQXn7Cb";
     *
     * // Execute //
     * $alpha_out  = alphaID($number_in, false, 8);
     * $number_out = alphaID($alpha_in, true, 8);
     *
     * if ($number_in != $number_out) {
     *   echo "Conversion failure, ".$alpha_in." returns ".$number_out." instead of the ";
     *   echo "desired: ".$number_in."\n";
     * }
     * if ($alpha_in != $alpha_out) {
     *   echo "Conversion failure, ".$number_in." returns ".$alpha_out." instead of the ";
     *   echo "desired: ".$alpha_in."\n";
     * }
     *
     * // Show //
     * echo $number_out." => ".$alpha_out."\n";
     * echo $alpha_in." => ".$number_out."\n";
     * echo alphaID(238328, false)." => ".alphaID(alphaID(238328, false), true)."\n";
     *
     * // expects:
     * // 2188847690240 => SpQXn7Cb
     * // SpQXn7Cb => 2188847690240
     * // aaab => 238328
     *
     * </code>
     *
     * @author  Kevin van Zonneveld &lt;kevin@vanzonneveld.net>
     * @author  Simon Franz
     * @author  Deadfish
     * @author  SK83RJOSH
     * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
     * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
     * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
     * @link    http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/
     *
     * @param mixed   $in   String or long input to translate
     * @param boolean $to_num  Reverses translation when true
     * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
     * @param string  $pass_key Supplying a password makes it harder to calculate the original ID
     *
     * @return mixed string or long
     */
    function crypt_id($in, $to_num = false, $pad_up = false, $pass_key = SALT)
    {
        if($in === false) {
            return false;
        }
        
        $out = '';
        $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($index);

        if($pass_key !== null)
        {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for($n = 0; $n < strlen($index); $n++)
            {
                $i[] = substr($index, $n, 1);
            }

            $pass_hash = hash('sha256', $pass_key);
            $pass_hash = (strlen($pass_hash) < strlen($index) ? hash('sha512', $pass_key) : $pass_hash);

            for($n = 0; $n < strlen($index); $n++)
            {
                $p[] = substr($pass_hash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        if($to_num)
        {
            // Digital number  <<--  alphabet letter code
            $len = strlen($in) - 1;

            for($t = $len; $t >= 0; $t--)
            {
                $bcp = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
            }

            if(is_numeric($pad_up))
            {
                $pad_up--;

                if($pad_up > 0)
                {
                    $out -= pow($base, $pad_up);
                }
            }
        } else {
            // Digital number  -->>  alphabet letter code
            if(is_numeric($pad_up))
            {
                $pad_up--;

                if($pad_up > 0)
                {
                    $in += pow($base, $pad_up);
                }
            }

            for($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--)
            {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
        }

        return $out;
    }
    
