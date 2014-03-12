<?php

    /**
     * File containing: Properties class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOS Open Source Association} 
     *
     * @since 24. July 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;
    
    use RescueMe\Locale;
    
    
    /**
     * Properties class
     * 
     * @package 
     */
    class Properties
    {
        const TABLE = "properties";
        
        const ALL = "all";
        const SHOW = "show";
        const HIDE = "hide";
        const TOP = "top";
        const BOTTOM = "bottom";
        const EXPANED = "expanded";
        const COLLAPSED = "collaped";
        
        const GET_URI = 'property/get';
        
        const PUT_URI = 'property/put';
        
        const OPTIONS_URI = 'property/options';
        
        const SYSTEM_COUNTRY = "system.country";
        
        const SYSTEM_PAGE_SIZE = "system.page.size";
        
        const LOCATION_MAX_WAIT = "location.max.wait";
        
        const LOCATION_MAX_AGE = "location.max.age";
        
        const LOCATION_DESIRED_ACC = "location.desired.accuracy";
        
        const LOCATION_APPCACHE = "location.appcache";
        const LOCATION_APPCACHE_VERSION = "version";
        const LOCATION_APPCACHE_SETTINGS = "settings";
        
        const TRACE_BAR_STATE = "trace.bar.state";
        
        const TRACE_BAR_LOCATION = "trace.bar.location";
        
        const TRACE_DETAILS = "trace.details";
        const TRACE_DETAILS_REFERENCE = "trace.reference";
        const TRACE_DETAILS_LOCATION = "trace.details.location";
        const TRACE_DETAILS_LOCATION_TIME = "trace.details.location.time";
        const TRACE_DETAILS_LOCATION_URL = "trace.details.location.url";
        
        const SMS_REQUIRE = 'sms.require';
        const SMS_REQUIRE_MULTIPLE = 'multiple';
        const SMS_REQUIRE_UNICODE = 'unicode';
        const SMS_REQUIRE_SENDER_ID_ALPHA = 'alpha';
        const SMS_REQUIRE_SENDER_ID_NUMERIC = 'numeric';        
        
        const SMS_SENDER_ID = 'sms.sender.id';
        
        const SMS_OPTIMIZE = 'sms.optimize';
        const SMS_OPTIMIZE_DELIVERY = 'delivery';
        const SMS_OPTIMIZE_ENCODING = 'encoding';
        
        const MAP_DEFAULT_BASE = 'map.default.base';
        const MAP_DEFAULT_BASE_TERRAIN = 'terrain';
        const MAP_DEFAULT_BASE_SATELLITE = 'satellite';
        const MAP_DEFAULT_BASE_HYBRID = 'hybrid';
        
        const MAP_DEFAULT_FORMAT = 'map.default.format';
        const MAP_DEFAULT_FORMAT_UTM = 'utm';
        const MAP_DEFAULT_FORMAT_DD = 'dd';
        const MAP_DEFAULT_FORMAT_DEM = 'dem';
        const MAP_DEFAULT_FORMAT_DMS = 'dms';
        const MAP_DEFAULT_FORMAT_6D = '6d';
        
        
        public static $meta = array(
            
            self::SYSTEM_COUNTRY => array(
                'type' => 'select',
                'default' => '',
                'options' => true,
                'description' => "Use given country as default country code (phone number prefix)."
            ),
            
            self::SYSTEM_PAGE_SIZE => array(
                'type' => 'text',
                'default' => 25,
                'options' => false,
                'description' => "Maximum number of lines per page (pagination)"
            ),
            
            self::LOCATION_MAX_AGE => array(
                'type' => 'text',
                'default' => 900000,
                'options' => false,
                'description' => "Don't use positions older than maximum age (milliseconds)."
            ),
            
            self::LOCATION_MAX_WAIT => array(
                'type' => 'text',
                'default' => 600000,
                'options' => false,
                'description' => "Give up finding location after maximum time (milliseconds)."
            ),
            
            self::LOCATION_DESIRED_ACC => array(
                'type' => 'text',
                'default' => 100,
                'options' => false,
                'description' => "Track until location with accuracy is found (meter)."
            ),
            
            self::LOCATION_APPCACHE => array(
                'type' => 'select',
                'default' => 'none',
                'options' => array(                   
                    'none' => 'None',
                    self::LOCATION_APPCACHE_SETTINGS => 'Settings'
                 ),
                'description' => "Make locate script available offline with HTML5 appcache."
            ),
            
            self::TRACE_BAR_STATE => array(
                'type' => 'select',
                'default' => self::EXPANED,
                'options' => array(                   
                    self::EXPANED => 'Expanded',
                    self::COLLAPSED => 'Collapsed',
                 ),
                'description' => "Trace trace bar layout state."
            ),
            
            self::TRACE_BAR_LOCATION => array(
                'type' => 'select',
                'default' => self::TOP,
                'options' => array(                   
                    self::TOP => 'Top',
                    self::BOTTOM => 'Bottom',
                 ),
                'description' => "Trace trace bar location."
            ),
            
            self::TRACE_DETAILS => array(
                'type' => 'checklist',
                'default' => '',
                'options' => array(                   
                    self::TRACE_DETAILS_REFERENCE => 'Operation reference',
                    self::TRACE_DETAILS_LOCATION => 'Last location',
                    self::TRACE_DETAILS_LOCATION_TIME => 'Last timestamp',
                    self::TRACE_DETAILS_LOCATION_URL => 'Location URL'
                 ),
                'description' => "Show trace details"
            ),
            
            self::SMS_SENDER_ID => array(
                'type' => 'text',
                'default' => SMS_FROM,
                'options' => false,
                'description' => "Use custom alphanumeric sms sender id (if supported)."
            ),
            
            self::SMS_OPTIMIZE => array(
                'type' => 'select',
                'default' => self::SMS_OPTIMIZE_DELIVERY,
                'options' => array(
                    self::SMS_OPTIMIZE_DELIVERY => 'Delivery', 
                    self::SMS_OPTIMIZE_ENCODING => 'Encoding'
                 ),
                'description' => "Use 'Delivery' to minimize delivery delay, use 'Encoding' otherwise."
            ),
            
            self::SMS_REQUIRE => array(
                'type' => 'checklist',
                'default' => self::SMS_REQUIRE_MULTIPLE,
                'options' => array(
                    self::SMS_REQUIRE_MULTIPLE => 'Multiple SMS',
                    self::SMS_REQUIRE_UNICODE => 'Unicode Support',
                    self::SMS_REQUIRE_SENDER_ID_ALPHA => 'Alpha Sender ID',
                    self::SMS_REQUIRE_SENDER_ID_NUMERIC => 'Numeric Sender ID'
                 ),
                'description' => "Features must be present for delivery to occur (if supported)."
            ),
            
            self::MAP_DEFAULT_BASE => array(
                'type' => 'select',
                'default' => self::MAP_DEFAULT_BASE_TERRAIN,
                'options' => array(
                    'statkart.topo2' => 'Norway Topo2', 
                    self::MAP_DEFAULT_BASE_TERRAIN => 'Terrain', 
                    self::MAP_DEFAULT_BASE_SATELLITE => 'Satellite',
                    self::MAP_DEFAULT_BASE_HYBRID => 'Hybrid'
                 ),
                'description' => "Use given basemap as default (on refresh)."
            ),
            
            self::MAP_DEFAULT_FORMAT => array(
                'type' => 'select',
                'default' => self::MAP_DEFAULT_FORMAT_UTM,
                'options' => array(                    
                    self::MAP_DEFAULT_FORMAT_UTM => 'Full UTM',
                    self::MAP_DEFAULT_FORMAT_6D => '6 digit MRGS',
                    self::MAP_DEFAULT_FORMAT_DD => 'Decimal degrees',
                    self::MAP_DEFAULT_FORMAT_DEM => 'Decimal minutes',
                    self::MAP_DEFAULT_FORMAT_DMS => 'Degrees, minutes, seconds'
                    
                 ),
                'description' => "Show coordinates using given map coordinate system"
            )
        );
        
        public static function getDefaults() {
            $defaults = array();
            self::$meta[self::SYSTEM_COUNTRY]['default'] = Locale::getDefaultCountryCode();
            self::$meta[self::TRACE_DETAILS]['default'] = implode(',', array_keys(self::$meta[self::TRACE_DETAILS]['options']));
            foreach(self::$meta as $name => $property){
                $defaults[$name] = $property['default'];
            }
            return $defaults;
        }
        
        
        /**
         * Get all properties
         * 
         * @param integer $user_id
         * 
         * @return boolean|\RescueMe\Module
         */
        public static function getAll($user_id=0)
        {
            $res = DB::select(self::TABLE, "*", "`user_id`=$user_id");
            
            $properties = self::getDefaults();
            
            if(!DB::isEmpty($res)) {
                while ($row = $res->fetch_assoc()) {
                    $properties[$row['name']] = $row['value']; 
                }
            }
            
            return empty($properties) ? false : $properties;
            
        }// getAll       
        
        
        /**
         * Check if property exists
         * 
         * @param string $name Property name
         * @param integer $user_id
         * 
         * @return boolean
         */
        public static function exists($name, $user_id=0) 
        {
            $res = DB::select(self::TABLE, "*", self::filter($name, $user_id));
            
            return !DB::isEmpty($res);
        }        
        
        
        /**
         * Get value of property with given name
         * 
         * @param string $name Property name
         * @param integer $user_id
         * 
         * @return boolean|mixed
         */
        public static function get($name, $user_id=0) {
            
            $defaults = self::getDefaults();
            
            if(isset($defaults[$name]) === FALSE) {
                return false;
            }
            
            $res = DB::select(self::TABLE, "`value`", self::filter($name, $user_id));
            
            if (DB::isEmpty($res)) {
                
                // Allow empty?
                switch($name) {
                    case self::SMS_REQUIRE:
                        return "";
                }
                
                return $defaults[$name];
            }
            
            $row = $res->fetch_row();
            
            return $row[0];
            
        }// get
        
        
        /**
         * Set value of property with given name
         * 
         * @param string $name
         * @param mixed $value
         * @param integer $user_id
         * 
         * @return boolean TRUE if success, FALSE otherwise
         */
        public static function set($name, $value, $user_id=0) {
            
            if(self::exists($name, $user_id)) {
            
                $values = array("name" => $name, "value" => $value);
            
                $res = DB::update(self::TABLE, $values, self::filter($name, $user_id)) !== false;
            } 
            else {
            
                $values = array("name" => $name, "value" => $value, "user_id" => $user_id);
                
                $res = DB::insert(self::TABLE, $values) !== false;
            }
            
            return ($res === TRUE || is_numeric($res) && $res > 0);
            
            
        }// set

        
        /**
         * Set value of property with given name
         * 
         * @param string $name
         * @param int $user_id
         * 
         * @return boolean TRUE if success, FALSE otherwise
         */
        public function delete($name, $user_id=0) {
            
            return DB::delete(self::TABLE, self::filter($name, $user_id));
            
        }// delete
        
        
        /**
         * Get value editor type
         * @param string $name property name
         * @return string|boolean
         */
        public static final function type($name) {
            return isset(self::$meta[$name]['type']) ? self::$meta[$name]['type'] : false;
        }
        
        
        /**
         * Get value text
         * 
         * @param string $name property name
         * @param integer $user_id
         * 
         * @return string|boolean
         */
        public static final function text($name, $user_id=0) {
            $value = self::get($name, $user_id);
            switch($name) {
                case self::SYSTEM_COUNTRY:
                    return Locale::getCountryName($value);
                case self::SMS_REQUIRE:
                case self::TRACE_DETAILS:
                    $selected = array();
                    if(empty($value)) {
                        return _("None");
                    }
                    foreach(explode(',', $value) as $option) {
                        $selected[] = self::$meta[$name]['options'][$option];
                    }
                    return implode("<br>", $selected);
                default:
                    return isset(self::$meta[$name]['options'][$value]) ? self::$meta[$name]['options'][$value] : $value;
            }
            return $value;
        }
        
        
        
        
        /**
         * Get value options source
         * 
         * @param string $name property name
         * @return string|boolean
         */
        public static final function source($name) {
            
            switch(self::$meta[$name]['type']) {
                case 'select':
                case 'checklist':
                    return "property/options?name=$name";
            }
            return false;
        }
        
        
        /**
         * Get property value options
         * 
         * @param string $name Property
         * @return type
         */
        public static final function options($name) {
            $options = array();
            switch($name) {
                case self::SYSTEM_COUNTRY:
                    foreach(Locale::getCountryNames(true) as $code => $country) {
                        $options[] = array('value' =>  $code, 'text' => $country);
                    }
                    break;
                default:
                    if(isset(self::$meta[$name]['options'])) {
                        foreach(self::$meta[$name]['options'] as $code => $text) {
                            $options[] = array('value' => $code, 'text' => $text);
                        }
                    }        
                    
                    break;
            }
            return empty($options) ? false : $options;
        }
        
        
        /**
         * Get property description
         * 
         * @param string $name Property
         * @return string
         */
        public static final function description($name) {
            return _(self::$meta[$name]['description']);
        }
        
        
        
        /**
         * Get properties as row definitions.
         * 
         * @param integer $user_id
         * @return array
         * 
         * @see insert_row
         */
        public static final function rows($user_id=0) {
            
            $rows = array(); 
            
            $properties = self::getAll($user_id);

            if($properties !== false) {

                $url = ADMIN_URI.self::PUT_URI."/$user_id";

                foreach($properties as $name => $value) {

                    $cells = array();

                    $cells[] = array('value' => _($name));

                    $type = self::type($name);

                    $source = self::source($name);
                    $source = ($source ? 'data-source="'.ADMIN_URI.$source.'"' : "");

                    $text = self::text($name,$user_id);
                    $attributes = 'data-type="'.$type.'" '.$source.' href="#" class="editable editable-click"';
                    $value  = '<a id="name" data-pk="'.$name.'" data-value="'.$value.'"'.'" data-url="'.$url.'"'.$attributes .'>'.$text.'</a>';
                    $cells[] = array('value' => $value);
                    
                    $text = self::description($name);
                    $cells[] = array('value' => '<div class="muted">'.$text.'</div>');

                    $rows[$name] = $cells;
                }
            }
            return $rows;
            
        }
        

        /**
         * Ensure value is not empty
         * 
         * @param string $name
         * @param mixed $value
         * 
         * @return mixed
         */
        public static final function ensure($name, $value) {
            switch($name) {
                case self::SMS_REQUIRE:
                case self::TRACE_DETAILS:
                    
                    if(is_array($value)) {
                        $value = implode(",", $value);
                    }

                    break;
                    
                default:
                    
                    if(empty($value)) {
                        $defaults = self::getDefaults();
                        $value = $defaults[$name];
                     }
                    
            }
            return $value;
        }        
        
        
        /**
         * Assert proerty value
         * 
         * @param string $name
         * @param mixed $value
         * 
         * @return boolean|string TRUE if allowed, error message otherwise.
         */
        public static final function accept($name, $value) {
            // Verify setting
            switch($name) {
                case self::SYSTEM_COUNTRY:

                    if(!Locale::accept($value)) {
                        return 'Locale "'.$value.'" not accepted';
                    }                        

                    break;

                case self::SYSTEM_PAGE_SIZE:
                case self::LOCATION_MAX_AGE:
                case self::LOCATION_MAX_WAIT:
                case self::LOCATION_DESIRED_ACC:

                    if(is_numeric($value) === false) {
                        return '"'.$value.'" is not a number';
                    }                        

                    break;
                    
                case self::SMS_REQUIRE:
                case self::TRACE_DETAILS:
                    
                    if(empty($value) === FALSE) {
                        return true;
                    }
                    
                case self::SMS_OPTIMIZE:
                case self::MAP_DEFAULT_BASE:
                case self::MAP_DEFAULT_FORMAT:
                case self::LOCATION_APPCACHE:
                case self::TRACE_BAR_STATE:
                case self::TRACE_BAR_LOCATION:
                    
                    $array = is_array($value) ? $value : explode(",", $value);
                    
                    foreach($array as $value)
                    {
                        if(!in_array($value, array_keys(self::$meta[$name]['options']))) {
                            return '"'.$value.'" is not allowed';
                        }                        
                    }                    
                    
                    if(!in_array($value, array_keys(self::$meta[$name]['options']))) {
                        return '"'.$value.'" is not allowed';
                    }
                    
                    break;
                
                // Any alphanumeric value allowed
                case self::SMS_SENDER_ID:
                    
                    if(!ctype_alnum($value)) {
                        return '"'.$value.'" is not alphanumeric';
                    }                        
                    
                    break;
                    
                default:

                    return 'Setting "'."$name=$value".'" is invalid';
            }            
            
            return true;
        }
                

        /**
         * Get property filter
         * 
         * @param string $name Property name
         * @param integer $user_id User id
         * 
         * @return string
         */
        private static function filter($name, $user_id) {
            return "`name`='$name' AND `user_id`=$user_id";
        }        

    }// Properties
