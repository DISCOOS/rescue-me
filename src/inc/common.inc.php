<?php
    
    use Psr\Log\LogLevel;    
    use RescueMe\Log\Logs;    
    use RescueMe\Position;
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
                    $format = '%1$03d° %2$02d\' %3$02d\'\'';
                    $lon = sprintf($format,
                        $lon['deg'],
                        $lon['min'],
                        $lon['sec']);
                    $format = '%1$02d° %2$02d \'%3$02d\'\'';
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
    
?>
