<?php
    
    /**
     * File containing: Mobile class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    namespace RescueMe;

    use \Psr\Log\LogLevel;
    use RescueMe\Log\Logs;
    use RescueMe\SMS\Check;
    use RescueMe\SMS\Provider;
    use RescueMe\SMS\T;

    /**
     * Mobile class
     * 
     * @package RescueMe
     */
    class Mobile
    {
        const TABLE = "mobiles";
        
        const SELECT = 'SELECT `mobiles`.*, `mobiles`.`trace_id`, `users`.`user_id`, `trace_type`, `trace_ref`, `trace_closed`, `trace_alert_country`, `trace_alert_number`, `users`.`name` FROM `mobiles`';
        
        const JOIN = 'LEFT JOIN `traces` ON `traces`.`trace_id` = `mobiles`.`trace_id` LEFT JOIN `users` ON `traces`.`user_id` = `users`.`user_id`';

        const COUNT = 'SELECT COUNT(*), `users`.`name` AS `user_name` FROM `mobiles`';
        
        private static $fields = array
        (
            "mobile_name",
            "mobile_country",
            "mobile_number",
            "mobile_locale",
            "mobile_hash",
            "mobile_alerted",
            "sms_text",
            "trace_id"
        );
        
        private static $update = array
        (
            "mobile_name",
            "mobile_country",
            "mobile_number",
            "mobile_locale",
            "mobile_hash",
            "sms_text"
        );

        public $id = -1;
        public $trace_id;
        public $trace_ref;
        public $user_id;
        public $user_name;

        public $alerted;
        public $responded;

        public $name;
        public $type;
        public $locale = DEFAULT_LOCALE;
        public $number;
        public $country;
        public $network_code;

        public $trace_alert_country;
        public $trace_alert_number;

        public $last_pos;
        public $last_acc;

        public $sms_sent;
        public $sms2_sent;
        public $sms_mb_sent;
        public $sms_delivered;
        public $sms_provider;
        public $sms_provider_ref;
        public $sms_text;

        public $errors = array();
        public $requests = array();
        public $positions = array();

        public static function filter($values, $operand) {
            
            $fields = array(
                '`mobiles`.`mobile_name`',
                '`users`.`name`',
                '`traces`.`trace_type`');

            return DB::filter($fields, $values, $operand);
            
        }
        
        private static function select($filter='', $admin = false, $start = 0, $max = false){
            
            $query  = Mobile::SELECT . ' ' . Mobile::JOIN;
            
            $where = $filter ? array($filter) : array();
            
            if($admin === false) {                
                $where[] = '`traces`.`user_id` = ' . User::currentId();
            } 
            
            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }
            
            $query .= ' ORDER BY `mobile_alerted` DESC';
            
            if($max !== false) {
                $query .=  " LIMIT $start, $max";
            }            
            
            return $query;
        }
        

        public static function countAll($filter='', $admin = false) {
            
            $query  = Mobile::COUNT . ' ' . Mobile::JOIN;
            
            $where = $filter ? array($filter) : array();
            
            if($admin === false) {                
                $where[] = '`traces`.`user_id` = ' . User::currentId();
            } 
            
            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }
            
            $res = DB::query($query);

            if (DB::isEmpty($res)) return false;
            
            $row = $res->fetch_row();
            return $row[0];
        }        
        
        
        public static function getAll($filter='', $admin = false, $start = 0, $max = false) {
            
            $select = Mobile::select($filter, $admin, $start, $max);
            
            $res = DB::query($select);

            if (DB::isEmpty($res)) 
                return false;
            
            $mobiles = array();
            while ($row = $res->fetch_assoc()) {                
                $id = $row['mobile_id'];
                $mobile = new Mobile();
                $mobiles[$id] = $mobile->set($id, $row);
            }
            return $mobiles;
        }        
        
        public static function count($id, $admin = false) {
            return Mobile::countAll('`mobile_id`=' . (int) $id, $admin);
        }
        

        /**
         * Get Mobile instance
         * 
         * @param integer $id Mobile id
         * @param boolean $admin Administrator flag
         *
         * @return \RescueMe\Mobile|boolean. Instance of \RescueMe\Mobile is success, FALSE otherwise.
         */
        public static function get($id, $admin = true){

            $res = DB::query(Mobile::select('`mobile_id`=' . (int) $id, $admin));
            
            if(DB::isEmpty($res)) {
                return false;
            }

            $row = $res->fetch_assoc();

            $mobile = new Mobile();
            return $mobile->set($id, $row);

        }// get


        /**
         * Set mobile data from mysqli_result.
         * 
         * @param integer $id Mobile id.
         * @param array $values Mobile values
         *
         * @return \RescueMe\Mobile
         */
        private function set($id, $values) {

            $this->id = (int)$id;

            foreach($values as $key => $val){
                if($key === 'name') {
                    $this->user_name = $val;
                } else {
                    $property = str_replace('mobile_', '', $key);
                    $this->$property = $val;
                }
            }
            
            // Hack: Find out why data type is string
            $this->user_id = (int)$this->user_id;            
            
            return $this;
        }


        /**
         * Add user
         * @param $name
         * @param $country
         * @param $number
         * @param $locale
         * @param $message
         * @param $trace_id
         * @return bool|Mobile
         */
        public static function add($name, $country, $number,  $locale, $message, $trace_id){

            if(empty($name) || empty($country) || empty($number)
                || empty($locale) || empty($message) || empty($trace_id)) {
                
                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required arguments are mobile",
                    array(
                        'file' => __FILE__,
                        'method' => 'add',
                        'params' => func_get_args(),
                        'line' => $line,
                    )
                );
                return false;
            }
            
            $trace = Trace::get($trace_id);
            
            if($trace === false) {
                
                return Mobile::error("Mobile not added. Trace $trace_id does not exist.");
            }

            $values = array(
                (string) $name,
                (string) $country,
                (int) $number,
                (string) $locale,
                // Make unique hash of intl phone number
                (string) sha1(SALT.$country.$number),
                "NOW()",
                $message,
                (int) $trace_id
            );
            $values = prepare_values(self::$fields, $values);

            $id = DB::insert(self::TABLE, $values);

            if($id === FALSE) {
                return Mobile::error('Failed to insert mobile');
            }

            // Reuse values (optimization)
            $values = array_exclude($values, 'mobile_alerted');
            $values = array_merge($values, $trace->getData());
            
            $mobile = new Mobile();
            $mobile->set($id, $values);
            
            Logs::write(
                Logs::TRACE, 
                LogLevel::INFO, 
                'Mobile ' . $id . ' created.',
                $values
            );
            
            return $mobile->sendSMS() ? $mobile : false;

        }// add

        /**
         * Load mobile data from database
         *
         * @param boolean $admin Administrator flag
         *
         * @return \RescueMe\Mobile|boolean. Instance of \RescueMe\Mobile is success, FALSE otherwise.
         */
        public function load($admin = true){

            $res = DB::query(Mobile::select('`mobile_id`=' . (int) $this->id, $admin));

            if(DB::isEmpty($res)) {
                return false;
            }

            $row = $res->fetch_assoc();

            return $this->set($this->id, $row);

        }// get


        /**
         * Update mobile
         * @param $name
         * @param $country
         * @param $number
         * @param $locale
         * @param $message
         * @return bool
         */
        public function update($name, $country, $number, $locale, $message){

            if(empty($name) || empty($country) || empty($number) || empty($locale) || empty($message)) {
                
                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required arguments are mobile",
                    array(
                        'file' => __FILE__,
                        'method' => 'update',
                        'params' => func_get_args(),
                        'line' => $line,
                    )
                );
                return false;
            }

            $values = prepare_values(Mobile::$update,
                array(
                    (string) $name,
                    (string) $country,
                    (int) $number,
                    (string) $locale,
                    // Make unique hash of intl phone number
                    (string) sha1(SALT.$country.$number),
                    (string) $message)
            );

            $res = DB::update(self::TABLE, $values, "`mobile_id` = $this->id");
            
            if($res) {
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO, 
                    'Mobile ' . $this->id . ' updated.',
                    $values
                );
            }
            else {
                Mobile::error('Failed to update mobile ' . $this->id);
            }                

            return $res;

        }// update
        
        // TODO: Merge with getPositions()!
        public function getAjaxPositions($num) {
            if($this->id === -1)
                return false;
            
            $query = "SELECT `pos_id` FROM `positions` WHERE `mobile_id` = " . (int) $this->id
                    . " ORDER BY `timestamp` LIMIT ".$num.",100";
            $res = DB::query($query);

            if(!$res) return false;
            
            $positions = array();
            while($row = $res->fetch_assoc()){
                $positions[] = new Position($row['pos_id']);
            }
            
            return $positions;
        } // getAjaxPositions


        /**
         * Get all positions found
         * @return array|bool
         */
        public function getPositions(){
            if($this->id === -1) {
                return false;
            }

            $query = "SELECT `pos_id`, `acc`, `timestamp` FROM `positions` "
                    . "WHERE `mobile_id` = " . (int) $this->id
                    . " ORDER BY `timestamp`";
            $res = DB::query($query);

            if(!$res) {
                return false;
            }

            $this->positions = array();
            while($row = $res->fetch_assoc()){
                $this->positions[] = new Position($row['pos_id']);
            }

            if(!is_array($this->positions) || count($this->positions) == 0) {
                $this->last_pos = new Position();
                $this->last_acc = -1;
            }
            else {
                $this->last_pos = $this->positions[(sizeof($this->positions)-1)];
                $this->last_acc = $this->last_pos->acc;
            }

            return $this->positions;
        }// getPositions

        /**
         * Get the most accurate position that's newer than a given minutes.
         * @param integer $maxAge How many minutes old.
         * @return boolean|array
         */
        public function getMostAccurate($maxAge = 15) {
            if($this->id === -1)
                return false;

            $query = "SELECT `pos_id`, `acc`, `lat`, `lon`, `timestamp` FROM `positions`" .
                    " WHERE `mobile_id` = %d AND `acc` %s AND `timestamp` > NOW() - INTERVAL %d MINUTE" .
                    " ORDER BY `acc` LIMIT 1";

            // Handle positions with no accuracy as least accurate
            $res = DB::query(sprintf($query, (int)$this->id, ">0", (int)$maxAge));

            if(DB::isEmpty($res)) {
                // Get least accurate
                $res = DB::query(sprintf($query, (int)$this->id, "=0", (int)$maxAge));
                if(DB::isEmpty($res)) {
                    return false;
                }
            }

            $row = $res->fetch_assoc();
            if ($row === NULL) return false;
            return $row;
        }

        /**
         * Get all requests
         * @return array|bool
         */
        public function getRequests(){

            if($this->id === -1) {
                return false;
            }

            $res = DB::select('requests', '*', "`mobile_id`=" . (int) $this->id, "`request_timestamp` DESC");

            if(DB::isEmpty($res)) {
                return false;
            }

            $this->requests = array();
            while($row = $res->fetch_assoc()){
                $this->requests[$row['request_id']] = $row;
            }

            return $this->requests;
        }// getRequests


        /**
         * Get all errors
         * @param bool $count Return array with count of each error number
         * @return array|bool
         */
        public function getErrors($count = false){

            if($this->id === -1) {
                return false;
            }

            $res = DB::select('errors', '*', "`mobile_id`=" . (int) $this->id);

            if(DB::isEmpty($res)) {
                return false;
            }

            $this->errors = array();
            while($row = $res->fetch_assoc()){
                $index = (string)$row['error_number'];
                if($count) {
                    if(isset($this->errors[$index])) {
                        $this->errors[$index]++;
                    } else {
                        $this->errors[$index] = 1;
                    }
                } else {
                    $this->errors[$row['error_id']] = $row;
                }

            }

            return $this->errors;
        }// getErrors


        /**
         * Register error
         * @param $number
         * @param $ua
         * @param $ip
         * @return bool
         */
        public function register($number, $ua, $ip) {

            $success = ($this->id !== -1);

            // Sanity check
            if($success && ($id = $this->addError($number))) {
                $success = $this->addRequest('error', $ua, $ip, $id) !== FALSE;
            }

            return $success;
        }


        /**
         * Register location
         * @param $lat
         * @param $lon
         * @param $acc
         * @param $alt
         * @param $timestamp
         * @param $ua
         * @param $ip
         * @return bool
         */
        public function located($lat, $lon, $acc, $alt, $timestamp, $ua, $ip){

            // Sanity check
            if($this->id === -1) return false;

            $this->last_pos = new Position();
            $this->last_pos->set(
                array(
                    'lat' => $lat,
                    'lon' => $lon,
                    'acc' => $acc,
                    'alt' => $alt,
                    'timestamp' => $timestamp
                )
            );
            $this->last_acc = $acc;
            
            $best_acc = $this->getMostAccurate();
            $best_acc = $best_acc ? (int)$best_acc['acc'] : 0;

            // Send SMS 2?
            if((int) $acc > 500 && sizeof($this->positions) > 1){
                
                // Update this object
                $this->get($this->id);

                // Is SMS2 already sent
                if($this->sms2_sent == 'false'){

                    $this->_sendSMSCoarseLocation();
                }
            }

            // Alert person of concern if an accurate position is logged
            // Always send first position and if the accuracy improves by 20%
            else if(($this->sms_mb_sent == 'false') || $best_acc === 0 || $acc < $best_acc * 0.8) {

                $this->_sendSMSLocationUpdate();

            }

            if(($id = $this->addPosition($lat, $lon, $acc, $alt, $timestamp)) !== false) {
                $this->addRequest('position', $ua, $ip, $id);
            }

            return true;

        }// located


        /**
         * Send message to mobile
         * @return array|bool|mixed|Mobile
         */
        public function sendSMS(){

            $ref = $this->_sendSMS($this->country, $this->number, $this->sms_text, true);
            
            if($ref === FALSE) {

                // TODO: Replace with proper error handling
                $this->_sendSMS(
                   $this->trace_alert_country,
                   $this->trace_alert_number,
                   T::_(T::ALERT_SMS_NOT_SENT, $this->locale),
                   false
                );
               
            } else {

                $user_id = User::currentId();
                if(isset($user_id) === false) {
                    $user_id = $this->user_id;
                }
                
                $module = Manager::get('RescueMe\SMS\Provider', $user_id);

                $dt = new \DateTime();
                $dt = "FROM_UNIXTIME({$dt->getTimestamp()})";


                $values = prepare_values(
                    array('sms_sent','sms_delivered'),
                    array($dt, 'NULL')
                );


                if(DB::update('mobiles', $values, "`mobile_id` = '{$this->id}'") === FALSE) {
                    Mobile::error('Failed to update SMS status for mobile ' . $this->id);
                }

                $values = prepare_values(
                    array(
                        'mobile_id',
                        'message_type',
                        'message_sent',
                        'message_provider',
                        'message_provider_ref',
                        'message_locale',
                        'message_text'),
                    array(
                        $this->id, 'sms', $dt, $module->impl, $ref, $this->locale, $this->sms_text
                    )
                );

                if(DB::insert('messages', $values) === FALSE) {
                    Mobile::error('Failed to insert SMS message');
                }

                // Load data from database
                $ref = $this->load();

            }

            return $ref;

        }// sendSMS

        
        /**
         * Check mobile state
         * 
         * @param integer $id Mobile id
         * @param boolean $admin
         * 
         * @return Mobile|boolean
         */
        public static function check($id, $admin = true) {
            
            // Is check required?
            if(($mobile = Mobile::get($id, $admin)) !== false) {

                $module = Manager::get('RescueMe\SMS\Provider', $mobile->user_id);

                /** @var Provider $sms */
                if(($sms = $module->newInstance()) instanceof Check) {

                    // Is LAST sent message pending? | 'sms_delivered' is reset each time a message is sent to mobile
                    if(empty($mobile->sms_delivered) === true) {

                        // Get last pending sms
                        $filter = "`message_delivered` IS NULL AND mobile_id=%s`";
                        $filter = sprintf($filter, $id);
                        // Sort pending messages on descending timestamp (latest first)
                        $sql = DB::select('messages', $filter, '`message_sent` DESC');
                        $res = DB::query($sql);
                        if(DB::isEmpty($res) === FALSE) {

                            $code = Locale::getDialCode($mobile->country);
                            $code = $sms->accept($code);

                            // Check all pending messages
                            while($row = $res->fetch_assoc()){
                                if($row['message_provider'] === $module->impl) {
                                    $number = $code.$mobile->number;
                                    /** @var Check $sms */
                                    if($sms->request($row['message_provider_ref'],$number)) {
                                        // Load results
                                        $mobile->load($admin);
                                    }
                                }
                            }

                        } else {
                            $context = array('sql' => $sql);
                            Mobile::error(T_('Failed to check message status for mobile') . $id, $context);
                        }
                    }
                }
            }
            
            return $mobile;
            
        }


        /**
         * Register response device from user_agent string and store client ip
         * @param string $ua
         * @param string $ip
         *
         * @return boolean
         */
        public function responded($ua, $ip) {

            $this->addRequest('response', $ua, $ip);

            $query = "UPDATE `mobiles` 
                        SET `mobile_responded` = NOW()
                      WHERE `mobile_id` = '" . $this->id . "';";

            $res = DB::query($query);

            if($res === FALSE) {
                $context = array(
                    'sql' => $query
                );
                Mobile::error(T_('Failed to update status to RESPONDED for mobile ') . $this->id, $context);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Mobile {$this->id} has loaded tracking page");
            }
            return $res;
        }


        private function addRequest($type, $ua, $ip, $foreign_id = 0) {

            $values = prepare_values(
                array(
                    'request_type',
                    'request_ua',
                    'request_client_ip',
                    'request_timestamp',
                    'mobile_id',
                    'foreign_id'
                ),
                array($type, $ua, $ip, 'NOW()', $this->id, $foreign_id)
            );

            if(($res = DB::insert('requests', $values)) === FALSE) {
                $context = array(
                    'values' => $values
                );
                Mobile::error(T_('Failed to add request from mobile ') . $this->id, $context);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Added request {$res} to mobile {$this->id} ");
            }
            return $res;
        }

        private function addError($number) {

            $values = prepare_values(
                array(
                    'error_number',
                    'mobile_id'
                ),
                array(
                    $number,
                    $this->id
                )
            );

            if(($res = DB::insert('errors', $values)) === FALSE) {
                $context = array(
                    'values' => $values
                );
                Mobile::error(T_('Failed to add error from mobile ') . $this->id, $context);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Added error {$res} to mobile {$this->id} ");
            }
            return $res;
        }

        /**
         * Anonymize mobile data
         *
         * @param string|boolean $name Name
         * 
         * @return boolean
         */
        public function anonymize($name='') {

            if(!$name) {
                $name = T_('Missing person');
            }

            $values = prepare_values(Mobile::$update, array("$name", '', ''));

            $res = DB::update(self::TABLE, $values, "`mobile_id` = $this->id");

            // TODO: Anonymize messages
            // TODO: Anonymize log entries

            if($res === FALSE) {
                Mobile::error('Failed to anonymize mobile ' . $this->id, $values);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Mobile {$this->id} has been anonymized");
            }

            return $res;
        }


        /**
         * Send SMS
         * 
         * @param string $country International phone number to sender
         * @param string $to Local phone number to recipient (without country dial code)
         * @param string $message Message string
         * @param boolean $mobile True if recipient is mobile
         * 
         * @return mixed|array Message id if success, errors otherwise (array).
         */
        private function _sendSMS($country, $to, $message, $mobile) {
            
            $user_id = User::currentId();
            if(isset($user_id) === false) {
                $user_id = $this->user_id;
            }

            /** @var Provider $sms */
            $sms = Manager::get(Provider::TYPE, $user_id)->newInstance();
            
            if($sms === FALSE)
            {
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    'Failed to get SMS provider. SMS not sent to mobile' . $this->id
                );
                return false;
            }

            // facebook-copy fix (includes 3 invisible chars..)
            if(strlen($to) == 11 && (int) $to == 0) {
                $to = substr($to, 3);
            }

            $params = Properties::getAll($user_id);
            $p = format_pos($this->last_pos, $params, false);
            
            $id = $mobile ? encrypt_id($this->id) : $this->id;
            
            $message = str_replace
            (
                array('%LINK%', '#mobile_id', '#to', '#m_name', '#acc', '#pos'),
                array(LOCATE_URL,  $id, $to, $this->name, $this->last_acc, $p),
                $message
            );

            $res = $sms->send($user_id, $country, $to, $message);
            
            if($res) {
                
                $context = array(
                    'from' => $user_id,
                    'country' => $country,
                    'to' => $to,
                );
                
                $recipient = $mobile ? "mobile $this->id" : " to operator of $this->id";
                
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO,
                    "SMS sent to $recipient ($to)",
                    $context
                );
                
            } else {
                
                $context = array(
                    'code' => $sms->errno(),
                    'error' => $sms->error()
                );
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    'Failed to send SMS to mobile ' . $this->id,
                    $context
                );
                
            }
            return $res;

        }// _sendSMS

        private static function error($message, $context = array())
        {
            $context['code'] = DB::errno();
            $context['error'] = DB::error();
            
            Logs::write(
                Logs::TRACE, 
                LogLevel::ERROR, 
                $message, 
                $context
            );
                
            return false;
        }

        private function addPosition($lat, $lon, $acc, $alt, $timestamp)
        {
            $values = prepare_values(
                array('mobile_id', 'lat', 'lon', 'acc', 'alt', 'timestamp_device'),
                array(
                    (int)$this->id,
                    (float)$lat,
                    (float)$lon,
                    (int)$acc,
                    (int)$alt,
                    date('Y-m-d H:i:s', $timestamp)
                )
            );

            // Insert new position
            $posID = DB::insert('positions', $values);

            if ($posID !== false) {

                $p = new Position($posID);
                $this->positions[] = $p;

                $user_id = User::currentId();
                if (isset($user_id) === false) {
                    $user_id = $this->user_id;
                }
                $params = Properties::getAll($user_id);
                $message = 'Mobile ' . $this->id . ' reported position ' . format_pos($p, $params);

                Logs::write(
                    Logs::TRACE,
                    LogLevel::INFO,
                    $message
                );

                unset($values['user_agent']);

                Logs::write(
                    Logs::LOCATION,
                    LogLevel::INFO,
                    $message,
                    $values
                );


            } else {

                Mobile::error('Failed to insert position for mobile ' . $this->id, $values);

            }

            return $posID;
        }

        private function _sendSMSCoarseLocation()
        {
            if ($this->_sendSMS(
                    $this->country,
                    $this->number,
                    T::_(T::ALERT_SMS_COARSE_LOCATION, $this->locale),
                    true
                ) === false
            ) {

                $context = array(
                    'country' => $this->country,
                    'mobile' => $this->number
                );

                Logs::write(
                    Logs::TRACE,
                    LogLevel::ERROR,
                    sprintf(T_('Failed to send second SMS to mobile %1$s'), $this->id),
                    $context
                );

            } else {

                $query = "UPDATE `mobiles` SET `sms2_sent` = 'true' WHERE `mobile_id` = '" . $this->id . "';";

                if (DB::query($query) === false) {
                    $context = array('sql' => $query);
                    Mobile::error(
                        sprintf(T_('Failed to update SMS status for mobile %1$s'), $this->id),
                        $context
                    );
                }

            }
        }

        private function _sendSMSLocationUpdate()
        {
            if ($this->_sendSMS(
                    $this->trace_alert_country,
                    $this->trace_alert_number,
                    T::_(T::ALERT_SMS_LOCATION_UPDATE, $this->locale),
                    false
                ) === false
            ) {

                Logs::write(
                    Logs::TRACE,
                    LogLevel::ERROR,
                    sprintf(T_('Failed to send SMS with position from mobile [%1$s]'), $this->id),
                    array($this->trace_alert_country, $this->trace_alert_number)
                );


            } else {

                $query = "UPDATE `mobiles` SET `sms_mb_sent` = 'true' WHERE `mobile_id` = '" . $this->id . "';";

                if (DB::query($query) === false) {
                    $context = array('sql' => $query);
                    Mobile::error('Failed to update SMS status for mobile ' . $this->id, $context);
                }
            }
        }

    }
