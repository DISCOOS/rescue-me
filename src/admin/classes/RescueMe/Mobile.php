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

    use Closure;
    use DateTime;
    use \Psr\Log\LogLevel;
    use ReflectionException;
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

        public $sms_text;
        public $sms_sent;
        public $sms2_sent;
        public $sms_mb_sent;
        public $sms_delivered;
        public $sms_provider;
        public $sms_provider_ref;

        public $errors = array();
        public $requests = array();
        public $positions = array();

        /**
         * Get filter for given values
         * @param $values
         * @param $operand
         * @return string
         */
        public static function filter($values, $operand) {
            
            $fields = array(
                '`mobiles`.`mobile_name`',
                '`users`.`name`',
                '`traces`.`trace_type`');

            return DB::filter($fields, $values, $operand);
            
        }

        /**
         * Get mobiles given filter and limits
         * @param string $filter
         * @param bool $admin
         * @param int $start
         * @param bool $max
         * @return string
         */
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


        /**
         * Get count of all mobiles
         * @param string $filter
         * @param bool $admin
         * @return bool
         * @throws DBException
         */
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

        /**
         * Get all mobiles
         * @param string $filter
         * @param bool $admin
         * @param int $start
         * @param bool $max
         * @return array|bool
         * @throws DBException
         */
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

        /**
         * Get number of mobiles
         * @param $id
         * @param bool $admin
         * @return bool
         * @throws DBException
         */
        public static function count($id, $admin = false) {
            return Mobile::countAll('`mobile_id`=' . (int) $id, $admin);
        }


        /**
         * Get Mobile instance
         *
         * @param integer $id Mobile id
         * @param boolean $admin Administrator flag
         *
         * @return Mobile|boolean. Instance of \RescueMe\Mobile is success, FALSE otherwise.
         * @throws DBException
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
         * @return Mobile
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
         * @throws DBException
         * @throws ReflectionException
         */
        public static function add($name, $country, $number,  $locale, $message, $trace_id){

            if(empty($name) || empty($country) || empty($number)
                || empty($locale) || empty($message) || empty($trace_id)) {
                return Mobile::log_trace_error(
                    T_('One or more required arguments are missing'),
                    array(
                        'file' => __FILE__,
                        'method' => 'add',
                        'params' => func_get_args(),
                        'line' => __LINE__,
                    )
                );
            }
            
            $trace = Trace::get($trace_id);
            
            if($trace === false) {
                return Mobile::log_trace_error(sentences(array(
                    T_('Mobile not added'),
                    sprintf(T_('Trace %s does not exist'), $trace_id)))
                );
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
                return Mobile::log_trace_error(
                    sprintf(T_('Failed to insert mobile for trace %s'), $trace_id),
                    array_merge(array('trace_id' => $trace_id), DB::last_error())
                );
            }

            // Reuse values (optimization)
            $values = array_exclude($values, 'mobile_alerted');
            $values = array_merge($values, $trace->getData());
            
            $mobile = new Mobile();
            $mobile->set($id, $values);

            Mobile::log_trace(sprintf(T_('Mobile %s created.'), $id),  $values);
            
            return $mobile->trace() ? $mobile : false;

        }// add

        /**
         * Load mobile data from database
         *
         * @param boolean $admin Administrator flag
         *
         * @return Mobile|boolean. Instance of \RescueMe\Mobile is success, FALSE otherwise.
         * @throws DBException
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
         * @throws DBException
         */
        public function update($name, $country, $number, $locale, $message){

            if(empty($name) || empty($country) || empty($number) || empty($locale) || empty($message)) {
                return Mobile::log_trace_error(
                    T_('One or more required arguments are missing'),
                    array(
                        'file' => __FILE__,
                        'method' => 'update',
                        'params' => func_get_args(),
                        'line' => __LINE__,
                    )
                );
            }

            $values = prepare_values(Mobile::$update,
                array(
                    (string) $name,
                    (string) $country,
                    (int) $number,
                    (string) $locale,
                    // Make unique hash of intl phone number
                    (string) sha1(SALT.$country.$number),
                    (string) $message
                )
            );

            $res = DB::update(self::TABLE, $values, "`mobile_id` = $this->id");

            return $res
                ? Mobile::log_trace(
                    sprintf(T_('Mobile %s updated'), $this->id))
                : Mobile::log_trace_error(
                    sprintf(T_('Failed to update  mobile %s'), $this->id),
                    array_merge(array('mobile_id' => $this->id), DB::last_error())
                );
            

        }// update


        /**
         * Get undelivered messages
         * @return array
         * @throws DBException
         */
        public function getUndeliveredMessages() {

            $messages = array();

            // Get last pending sms
            $filter = "`message_delivered` IS NULL AND mobile_id=%s";
            $filter = sprintf($filter, $this->id);

            // Sort pending messages on descending timestamp (latest first)
            $res = DB::select('messages', '*', $filter, '`message_sent` DESC');
            if(DB::isEmpty($res) === FALSE) {

                // Check all pending messages
                while($row = $res->fetch_assoc()){
                    array_push($messages, $row);
                }
            }
            return $messages;
        }



        /**
         * Get positions for AJAX response
         *
         * TODO: Merge with getPositions()!
         *
         * @param $num
         * @return array|bool
         * @throws DBException
         */
        public function getAjaxPositions($num) {
            //
            if($this->id === -1)
                return false;

            $query = "SELECT `pos_id` FROM `positions` WHERE `mobile_id` = " . (int) $this->id
                    . " ORDER BY `timestamp` LIMIT $num,100";
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
         * @throws DBException
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
         * @throws DBException
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
         * @throws DBException
         */
        public function getRequests(){

            if($this->id === -1) {
                return false;
            }

            $res = DB::select('requests', '*',
                sprintf("`mobile_id`=%s, `request_timestamp` DESC", $this->id)
            );

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
         * @throws DBException
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
         * @param $number int Error number
         * @param $ua string User agent string
         * @param $ip string Client ip address
         * @return bool
         * @throws DBException
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
         * @throws DBException
         * @throws ReflectionException
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

                    $this->_sendCoarseLocationUpdate();
                }
            }

            // Alert person of concern if an accurate position is logged
            // Always send first position and if the accuracy improves by 20%
            else if(($this->sms_mb_sent == 'false') || $best_acc === 0 || $acc < $best_acc * 0.8) {

                $this->_sendLocationUpdate();

            }

            if(($id = $this->addPosition($lat, $lon, $acc, $alt, $timestamp)) !== false) {
                $this->addRequest('position', $ua, $ip, $id);
            }

            return true;

        }// located


        /**
         * Send trace SMS to mobile
         * @return bool
         * @throws DBException
         * @throws ReflectionException
         */
        public function trace() {

            $res = $this->_send($this->sms_text, true, function() {
                return sprintf(T_('SMS not sent to mobile %s'), $this->id);
            });

            if($res === FALSE) {
                return Mobile::log_trace_error(
                    sprintf(T_('Failed to trace mobile %s'), $this->id));
            }

            $dt = new DateTime();
            $dt = DB::timestamp($dt->getTimestamp());

            // Reset to 'sent' state
            $values = prepare_values(
                array('sms_sent','sms_delivered'),
                array($dt, 'NULL')
            );


            if(DB::update('mobiles', $values, "`mobile_id` = '{$this->id}'") === FALSE) {
                Mobile::log_trace_error(
                    sprintf(T_('Failed to update SMS status for mobile %s'), $this->id),
                    DB::last_error()
                );
            }

            // Load data from database
            return $this->load();

        }// send


        /**
         * Check mobile state
         *
         * @param integer $id Mobile id
         * @param boolean $admin
         *
         * @return Mobile|boolean
         * @throws DBException
         * @throws ReflectionException
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
                        $filter = "`message_delivered` IS NULL AND mobile_id=%s";
                        $filter = sprintf($filter, $id);
                        // Sort pending messages on descending timestamp (latest first)
                        $res = DB::select('messages', '*', $filter, '`message_sent` DESC');
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
                            Mobile::log_trace_error(
                                sprintf(T_('Failed to check message status for mobile %s'), $id),
                                array_merge(array(
                                    'table' => 'messages',
                                    'fields' => '*',
                                    'filter' => $filter
                                ), DB::last_error())
                            );
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
         * @throws DBException
         */
        public function responded($ua, $ip) {

            $this->addRequest('response', $ua, $ip);

            $query = "UPDATE `mobiles` SET `mobile_responded` = NOW() WHERE `mobile_id` = '$this->id';";

            $res = DB::query($query);

            return $res
                ? Mobile::log_trace(
                    sprintf(T_('Mobile %s has loaded tracking page'), $this->id))
                : Mobile::log_trace_error(
                    sprintf(T_('Failed to update status to RESPONDED for mobile %s'), $this->id),
                        array_merge(array('sql' => $query), DB::last_error())
                );
        }


        /**
         * Add request from mobile
         * @param $type
         * @param $ua
         * @param $ip
         * @param int $foreign_id
         * @return bool
         * @throws DBException
         */
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

            $res = DB::insert('requests', $values);

            return $res
                ? Mobile::log_trace(
                    sprintf(T_('Added request %s to mobile %s'), $res, $this->id), $values)
                : Mobile::log_trace_error(
                    sprintf(T_('Failed to add request from mobile %s'), $this->id),
                    array_merge($values, DB::last_error())
                );
        }


        /**
         * Add trace error from mobile
         * @param $number
         * @return bool
         * @throws DBException
         */
        private function addError($number) {

            $values = prepare_values(
                array('error_number', 'mobile_id' ),
                array($number, $this->id)
            );

            $res = DB::insert('errors', $values);

            return $res
                ? Mobile::log_trace(
                    sprintf(T_('Added error %s to mobile %s'), $res, $this->id), $values)
                : Mobile::log_trace_error(
                    sprintf(T_('Failed to add error from mobile %s'), $this->id),
                    array_merge($values, DB::last_error())
                );
        }

        /**
         * Anonymize mobile data
         *
         * @param string|boolean $name Name
         *
         * @return boolean
         * @throws DBException
         */
        public function anonymize($name = '') {

            if(!$name) {
                $name = T_('Missing person');
            }

            $values = prepare_values(Mobile::$update, array("$name", '', ''));

            $res = DB::update(self::TABLE, $values, "`mobile_id` = $this->id");

            // TODO: Anonymize messages
            // TODO: Anonymize log entries

            return $res
                ? Mobile::log_trace(
                    sprintf(T_('Mobile %s has been anonymized'),$this->id), $values)
                : Mobile::log_trace_error(
                    sprintf(T_('Failed to anonymize mobile %s'), $this->id),
                    array_merge($values, DB::last_error())
                );
        }


        /**
         * Send message as SMS
         *
         * @param string $message Message string with optional placeholders:
         *        [%LINK%, #mobile_id', '#to', '#m_name', '#acc', '#pos']
         * @param bool $encrypt Encrypt mobile id*
         * @param $on_error Closure that returns string logged with error message
         *
         * @return bool TRUE if sent, FALSE otherwise.
         *
         * @throws DBException
         * @throws ReflectionException
         */
        private function _send($message, $encrypt, $on_error) {

            // In case user_id is not set in current session
            $user_id = $this->ensureUserId();

            /** @var Provider $provider */
            if(FALSE === ($provider = $this->getProvider($user_id, $on_error))){
                return false;
            };
            
            $params = $this->build($message, $user_id, $encrypt);
            list ($country, $number, $text, $client_ref) = $params;

            $refs = $provider->send($country, $number, $text, $this->locale, $client_ref, function () {
                return sprintf(T_('SMS not sent to mobile %s'), $this->id);
            });

            return $refs !== FALSE;

        }// _send

        /**
         * Ensure an user id.
         *
         * If no user is available in current session, stored user_id is used
         *
         * @return int|null
         */
        private function ensureUserId(){
            $user_id = User::currentId();
            if(isset($user_id) === false) {
                $user_id = $this->user_id;
            }
            return $user_id;
        }

        /**
         * Build message parameters
         * @param string $text SMS text
         * @param bool $encrypt Encrypt mobile id
         * @param int $user_id User id
         * @return array of values $country, $number, $text, $client_ref,
         * @throws DBException
         */
        private function build($text, $encrypt, $user_id) {

            // Get country code and number to use
            $country = $encrypt ? $this->country : $this->trace_alert_country;
            $number = $this->fix($encrypt ? $this->number : $this->trace_alert_number);

            $params = Properties::getAll($user_id);
            $p = format_pos($this->last_pos, $params, false);

            $crypt_id = encrypt_id($this->id);
            $mobile_id = $encrypt ? $crypt_id : $this->id;
            $date = new DateTime();
            $client_ref = "$crypt_id-{$date->getTimestamp()}";

            // Replace known placeholders with actual values
            $text = str_replace
            (
                array('%LINK%', '#mobile_id', '#to', '#m_name', '#acc', '#pos'),
                array(LOCATE_URL,  $mobile_id, $number, $this->name, $this->last_acc, $p),
                $text
            );

            return array($country, $number, $text, $client_ref);

        }

        /**
         * Get provider for given user id
         * @param $user_id
         * @param $on_error Closure that returns string logged with error message
         * @return Provider
         * @throws DBException
         * @throws ReflectionException
         */
        private function getProvider($user_id, $on_error) {

            /** @var Provider $provider */
            $provider = Manager::get(Provider::TYPE, $user_id)->newInstance();

            if($provider === FALSE)
            {
                Mobile::log_trace_error(
                    sentences(array(
                        sprintf(T_('Failed to get SMS provider for %s'), $user_id),
                        call_user_func($on_error))
                    )
                );
            }

            return $provider;

        }

        /**
         * Log trace message
         * @param string $message Log message
         * @param array $context Log context
         * @return bool Returns always TRUE
         * @throws DBException
         */
        private static function log_trace($message, $context = array())
        {
            Logs::write(
                Logs::TRACE,
                LogLevel::INFO,
                $message,
                $context
            );

            return true;
        }

        /**
         * Log trace error message
         * @param string $message Log message
         * @param array $context Log context
         * @return bool Returns always FALSE
         * @throws DBException
         */
        private static function log_trace_error($message, $context = array())
        {
            Logs::write(
                Logs::TRACE, 
                LogLevel::ERROR, 
                $message, 
                $context
            );
                
            return false;
        }

        /**
         * Add new position
         * @param $lat
         * @param $lon
         * @param $acc
         * @param $alt
         * @param $timestamp
         * @return bool|int
         * @throws DBException
         */
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
            $pos_id = DB::insert('positions', $values);

            if ($pos_id === false) {
                return Mobile::log_location_error(
                    sprintf(T_('Failed to insert position for mobile %s'),$this->id),
                    array_merge($values, DB::last_error())
                );
            }

            $p = new Position($pos_id);
            $this->positions[] = $p;

            $user_id = $this->ensureUserId();

            $this::log_location(sprintf(
                T_('Mobile %s reported position %s'),
                $this->id , format_pos($p, Properties::getAll($user_id))
            ));

            return $pos_id;
        }


        /**
         * Log location message
         * @param string $message Log message
         * @param array $context Log context
         * @return bool Returns always TRUE
         * @throws DBException
         */
        private static function log_location($message, $context = array())
        {
            Logs::write(
                Logs::LOCATION,
                LogLevel::INFO,
                $message,
                $context
            );

            return true;
        }

        /**
         * Log location error
         * @param string $message Log message
         * @param array $context Log context
         * @return bool Returns always TRUE
         * @throws DBException
         */
        private static function log_location_error($message, $context = array())
        {
            Logs::write(
                Logs::LOCATION,
                LogLevel::ERROR,
                $message,
                $context
            );

            return true;
        }


        /**
         * Send second SMS to traced mobile when coarse location is received
         * @throws DBException
         * @throws ReflectionException
         */
        private function _sendCoarseLocationUpdate()
        {
            $message = T::_(T::ALERT_SMS_COARSE_LOCATION, $this->locale);

            $res = $this->_send($message, true, function (){
                return sprintf(T_('Failed to send second SMS to mobile %1$s'), $this->id);
            });

            if ($res !== FALSE) {

                $query = "UPDATE `mobiles` SET `sms2_sent` = 'true' WHERE `mobile_id` = '$this->id';";

                if (DB::query($query) === false) {
                    $context = array('sql' => $query);
                    Mobile::log_trace_error(
                        sprintf(T_('Failed to update `sms2_sent` for mobile %$s'), $this->id),
                        $context
                    );
                }
            }
        }


        /**
         * Send location update to mobile number of operator
         * @throws DBException
         * @throws ReflectionException
         */
        private function _sendLocationUpdate()
        {
            $message = T::_(T::ALERT_SMS_LOCATION_UPDATE, $this->locale);

            $res = $this->_send($message, false, function (){
                return sprintf(T_('Failed to send location update SMS to mobile %1$s'), $this->id);
            });

            if ($res !== FALSE) {

                $query = "UPDATE `mobiles` SET `sms_mb_sent` = 'true' WHERE `mobile_id` = '$this->id';";

                if (DB::query($query) === false) {
                    $context = array('sql' => $query);
                    Mobile::log_trace_error(
                        sprintf(T_('Failed to update `sms_mb_sent` for mobile %$s'), $this->id),
                        $context
                    );
                }
            }
        }

        /**
         * Facebook-copy fix (removes 3 invisible chars)
         * @param $number
         * @return bool|string
         */
        private function fix($number)
        {
            if (strlen($number) == 11 && (int)$number == 0) {
                $number = substr($number, 3);
            }
            return $number;
        }

    }
