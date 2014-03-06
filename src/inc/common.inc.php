<?php
    
    use Psr\Log\LogLevel;    
    use RescueMe\Log\Logs;    
    use RescueMe\Properties;

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
            Logs::write($log, $level, $message. "Missing values: ". implode(", ", $missing));
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
    
    
    function modules_exists($module, $_ = null) {
        
        $missing = array();
        
        foreach(func_get_args() as $module) {
            if(!RescueMe\Module::exists($module))
            {
                $missing[] = $module;
            }
        }    
        
        if(defined('USE_SILEX') && USE_SILEX)
        	return empty($missing);
        
        if(!empty($missing)) {
            insert_errors(_("Missing modules").' ( <a href="'.ADMIN_URI.'setup">'. _("Configure"). "</a>): ", $missing);
        }
        
        return empty($missing);
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
        return date(date('Y', $time) === date('Y') ? 'd.M H:i' : $timestamp, strtotime($timestamp));
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
            $since = "~"._("sec");
            if($ts > 0) {
                if($ts < 60) {
                    $since = "$ts "._("sec");
                }
                else if($ts < 2*60*60) {
                    $since = (int)($ts/60)." "._("min");                        
                }
                else {
                    $since = format_dt($timestamp);
                }
            }        
        } else {
            $since = _('Ukjent');
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
     * @param string $format Position format
     * @param boolean $label Format as label
     */
    function format_pos($p, $format = 'utm', $label = true) {
        
        if(isset($p) === false) {
            $success = false;
            $position = _('Aldri posisjonert');
        } else {
            $success = true;
            switch($format) {
                default:
                case Properties::MAP_DEFAULT_FORMAT_UTM:
                    $gPoint = new gPoint();
                    $gPoint->setLongLat($p->lon, $p->lat);
                    $gPoint->convertLLtoTM();
                    $format = '%1$s %2$07dE %3$07dN';
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

                    $format = '%1$03d %2$03d';
                    $position = sprintf($format,
                        $e,
                        $n
                    );
                    break;
                case Properties::MAP_DEFAULT_FORMAT_DD:
                    $format = '%1$sE %2$sN';
                    $position = sprintf($format,
                        $p->lon,
                        $p->lat
                    );
                    break;                
                case Properties::MAP_DEFAULT_FORMAT_DEM:
                    $lon = dec_to_dem($p->lon);
                    $lat = dec_to_dem($p->lat);                
                    $format = '%1$02d° %2$02d.%3$.4s';
                    $lon = sprintf($format,
                        $lon['deg'],
                        $lon['min'],
                        (string)$lon['des']);
                    $format = '%1$02d° %2$2d.%3$.4s';
                    $lat = sprintf($format,
                        $lat['deg'],
                        $lat['min'],
                        (string)$lat['des']);
                    $format = '%1$sE %2$sN';
                    $position = sprintf($format,
                        $lon,
                        $lat
                     );
                    break;
                case Properties::MAP_DEFAULT_FORMAT_DMS:
                    $lon = dec_to_dms($p->lon);
                    $lat = dec_to_dms($p->lat);
                    $format = '%1$03d° %2$02d\' %3$02.0f\'\'';
                    $lon = sprintf($format,
                        $lon['deg'],
                        $lon['min'],
                        $lon['sec']);
                    $format = '%1$02d° %2$02d \'%3$02.0f\'\'';
                    var_dump($lat);
                    $lat = sprintf($format,
                        $lat['deg'],
                        $lat['min'],
                        $lat['sec']);
                    
                    $format = '%1$sE %2$sN';
                    $position = sprintf($format,
                        $lon,
                        $lat
                     );
                    break;
            }
        }
        
        if($label) {
            $type = $success ? 'label-success' : 'label-warning';
            $position = '<span class="label '.$type.' label-position">'. $position. '</span>';
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
        $index = 'x7dHFEqtzhWQU19iZnL3rXmSpyJu68bIcOsa45EKVlPMAgj02CkvBeNfwoYTDG';
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
    