<?php
    
    /**
     * File containing: Missing class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    namespace RescueMe;

    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;
    use RescueMe\SMS\Check;

    /**
     * Missing class
     * 
     * @package RescueMe
     */
    class Missing
    {
        const TABLE = "missing";
        
        const SELECT = 'SELECT `missing`.*, `missing`.`op_id`, `users`.`user_id`, `op_type`, `op_ref`, `op_closed`, `alert_mobile_country`, `alert_mobile`, `users`.`name` FROM `missing`';
        
        const JOIN = 'LEFT JOIN `operations` ON `operations`.`op_id` = `missing`.`op_id` LEFT JOIN `users` ON `operations`.`user_id` = `users`.`user_id`';

        const COUNT = 'SELECT COUNT(*), `users`.`name` AS `user_name` FROM `missing`';
        
        private static $fields = array
        (
            "missing_name", 
            "missing_mobile_country", 
            "missing_mobile", 
            "missing_locale", 
            "missing_reported",
            "op_id",
            "sms_text"
        );
        
        private static $update = array
        (
            "missing_name", 
            "missing_mobile_country", 
            "missing_mobile",
            "missing_locale", 
            "sms_text"
        );

        public $id = -1;
        public $op_id;
        public $op_ref;
        public $user_id;
        public $user_name;

        public $answered;
        public $reported;

        public $name;
        public $type;
        public $locale = DEFAULT_LOCALE;
        public $mobile;
        public $mobile_country;
        
        public $alert_mobile;
        public $alert_mobile_country;

        public $last_pos;
        public $last_acc;

        public $sms2_sent;
        public $sms_mb_sent;
        public $sms_delivery;
        public $sms_provider;
        public $sms_provider_ref;
        public $sms_text;

        public $positions = array();
        
        public static function filter($values, $operand) {
            
            $fields = array(
                '`missing`.`missing_name`', 
                '`users`.`name`',
                '`operations`.`op_type`');

            return DB::filter($fields, $values, $operand);
            
        }
        
        private static function select($filter='', $admin = false, $start = 0, $max = false){
            
            $query  = Missing::SELECT . ' ' . Missing::JOIN;
            
            $where = $filter ? array($filter) : array();
            
            if($admin === false) {                
                $where[] = '`operations`.`user_id` = ' . User::currentId();
            } 
            
            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }
            
            $query .= ' ORDER BY `missing_reported` DESC';
            
            if($max !== false) {
                $query .=  " LIMIT $start, $max";
            }            
            
            return $query;
        }
        

        public static function countAll($filter='', $admin = false) {
            
            $query  = Missing::COUNT . ' ' . Missing::JOIN;
            
            $where = $filter ? array($filter) : array();
            
            if($admin === false) {                
                $where[] = '`operations`.`user_id` = ' . User::currentId();
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
            
            $select = Missing::select($filter, $admin, $start, $max);
            
            $res = DB::query($select);

            if (DB::isEmpty($res)) 
                return false;
            
            $missings = array();
            while ($row = $res->fetch_assoc()) {                
                $id = $row['missing_id'];
                $missing = new Missing();
                $missings[$id] = $missing->set($id, $row);
            }
            return $missings;
        }        
        
        public static function count($id, $admin = false) {
            return Missing::countAll('`missing_id`=' . (int) $id, $admin);
        }
        

        /**
         * Get Missing instance
         * 
         * @param integer $id Missing id
         * @param boolean $admin Administrator flag
         *
         * @return \RescueMe\Missing|boolean. Instance of \RescueMe\Missing is success, FALSE otherwise.
         */
        public static function get($id, $admin = true){

            $res = DB::query(Missing::select('`missing_id`=' . (int) $id, $admin));
            
            if(DB::isEmpty($res)) {
                return false;
            }

            $row = $res->fetch_assoc();

            $missing = new Missing();
            return $missing->set($id, $row);

        }// get


        /**
         * Set missing data from mysqli_result.
         * 
         * @param integer $id Missing id.
         * @param array $values Missing values
         *
         * @return \RescueMe\Missing
         */
        private function set($id, $values) {

            $this->id = (int)$id;

            foreach($values as $key => $val){
                if($key === 'name') {
                    $this->user_name = $val;
                } else {
                    $property = str_replace('missing_', '', $key);                
                    $this->$property = $val;
                }
            }
            
            // Hack: Find out why data type is string
            $this->user_id = (int)$this->user_id;            
            
            return $this;
        }


        public static function add($m_name, $m_mobile_country, $m_mobile,  $m_locale, $sms_text, $op_id){

            if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile) || empty($m_locale) || empty($op_id) || empty($sms_text)) {
                
                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required arguments are missing", 
                    array(
                        'file' => __FILE__,
                        'method' => 'add',
                        'params' => func_get_args(),
                        'line' => $line,
                    )
                );
                return false;
            }
            
            $operation = Operation::get($op_id);
            
            if($operation === false) {
                
                return Missing::error("Missing not added. Operation $op_id does not exist.");
            }
            

            $values = array(
                (string) $m_name, 
                (string) $m_mobile_country, 
                (int)$m_mobile, 
                (string)$m_locale, 
                "NOW()", 
                (int) $op_id, 
                $sms_text
            );
            $values = prepare_values(self::$fields, $values);

            $id = DB::insert(self::TABLE, $values);

            if($id === FALSE) {
                return Missing::error('Failed to insert missing');
            }

            // Reuse values (optimization)
            $values = array_exclude($values, 'missing_reported');            
            $values = array_merge($values, $operation->getData());
            
            $missing = new Missing();
            $missing->set($id, $values);
            
            Logs::write(
                Logs::TRACE, 
                LogLevel::INFO, 
                'Missing ' . $id . ' created.', 
                $values
            );
            
            return $missing->sendSMS() ? $missing : false;

        }// add


        public function update($m_name, $m_mobile_country, $m_mobile, $m_locale, $sms_text){

            if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile) || empty($m_locale) || empty($sms_text)) {
                
                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required arguments are missing", 
                    array(
                        'file' => __FILE__,
                        'method' => 'update',
                        'params' => func_get_args(),
                        'line' => $line,
                    )
                );
                return false;
            }

            $values = prepare_values(Missing::$update, array($m_name, $m_mobile_country, $m_mobile, $m_locale, $sms_text));

            $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");
            
            if($res) {
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO, 
                    'Missing ' . $this->id . ' updated.',
                    $values
                );
            }
            else {
                Missing::error('Failed to update missing ' . $this->id);
            }                

            return $res;

        }// update
        
        // TODO: Merge with getPositions()!
        public function getAjaxPositions($num) {
            if($this->id === -1)
                return false;
            
            $query = "SELECT `pos_id` FROM `positions` WHERE `missing_id` = " . (int) $this->id
                    . " ORDER BY `timestamp` LIMIT ".$num.",100";
            $res = DB::query($query);

            if(!$res) return false;
            
            $positions = array();
            while($row = $res->fetch_assoc()){
                $positions[] = new Position($row['pos_id']);
            }
            
            return $positions;
        } // getAjaxPositions


        public function getPositions(){
            if($this->id === -1) {
                return false;
            }

            $query = "SELECT `pos_id`, `acc`, `timestamp` FROM `positions` "
                    . "WHERE `missing_id` = " . (int) $this->id
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
                    " WHERE `missing_id` = " . (int) $this->id .
                    " AND `timestamp` > NOW() - INTERVAL ".(int)$maxAge." MINUTE" .
                    " ORDER BY `acc` LIMIT 1";
            
            $res = DB::query($query);

            if(!$res) return false;
            $row = $res->fetch_assoc();
            if ($row === NULL) return false;
            return $row;
        }

        public function addPosition($lat, $lon, $acc, $alt, $timestamp, $useragent = ''){

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
            $best_acc = $best_acc['acc'];

            // Send SMS 2?
            if((int) $acc > 500 && sizeof($this->positions) > 1){
                
                // Update this object
                $this->get($this->id);

                // Is SMS2 already sent
                if($this->sms2_sent == 'false'){
                    
                    if($this->_sendSMS(
                        $this->mobile_country, 
                        $this->mobile, 
                        T_locale(DOMAIN_SMS, $this->locale, 'ALERT_SMS_2'), 
                        true) === FALSE) {
                        
                        $context = array(
                            'country' => $this->mobile_country, 
                            'mobile' => $this->mobile
                        );
                        
                        Logs::write(
                            Logs::TRACE, 
                            LogLevel::ERROR, 
                            'Failed to send second SMS to missing ' . $this->id,
                            $context
                        );
                        
                    } else {
                        
                        $query = "UPDATE `missing` SET `sms2_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";
                        
                        if(DB::query($query) === FALSE){
                            $context = array('sql' => $query);
                            Missing::error('Failed to update SMS status for missing ' . $this->id, $context);
                        }
                        
                    }
                }
            }

            // Alert person of concern if an accurate position is logged
            // Always send first position and if the accuracy improves by 20%
            else if(($this->sms_mb_sent == 'false') || $acc < $best_acc * 0.8) {

                if($this->_sendSMS(
                    $this->alert_mobile_country, 
                    $this->alert_mobile, 
                    T_locale(DOMAIN_SMS, $this->locale, 'ALERT_SMS_LOCATION_UPDATE'), false) === FALSE) {
                    
                    Logs::write(
                        Logs::TRACE, 
                        LogLevel::ERROR, 
                        'Failed to send SMS with position from missing ' . $this->id,
                        array($this->alert_mobile_country, $this->alert_mobile)
                    );
                    
                    
                } else {

                    $query = "UPDATE `missing` SET `sms_mb_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";

                    if(DB::query($query) === FALSE) {
                        $context = array('sql' => $query);
                        Missing::error('Failed to update SMS status for missing ' . $this->id, $context);
                    }
                }

            }

            // Insert new position
            $values = prepare_values(array('missing_id', 'lat', 'lon', 'acc', 'alt', 'timestamp_device', 'user_agent'), 
                array(
                    (int) $this->id,
                    (float)$lat,
                    (float)$lon,
                    (int) $acc,
                    (int) $alt,
                    date('Y-m-d H:i:s', $timestamp),
                    $useragent
                )
            );

            $posID = DB::insert('positions', $values);
            
            if($posID !== FALSE) {               
                
                $p = new Position($posID);
                $this->positions[] = $p;
                
                $user_id = User::currentId();
                if(isset($user_id) === false) {
                    $user_id = $this->user_id;
                }
                $format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);
                $message = 'Missing ' . $this->id . ' reported position ' . format_pos($p, $format);
                
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
                
                Missing::error('Failed to insert position for missing ' . $this->id, $values);
                
            }            

        }// addPosition


        public function sendSMS(){

            $res = $this->_sendSMS($this->mobile_country, $this->mobile, $this->sms_text, true);
            
            if($res === FALSE) {
                
               $this->_sendSMS(
                   $this->alert_mobile_country, 
                   $this->alert_mobile,
                   T_locale(DOMAIN_SMS, $this->locale, 'ALERT_SMS_NOT_SENT'), 
                   false
               );               
               
            } else {

                $user_id = User::currentId();
                if(isset($user_id) === false) {
                    $user_id = $this->user_id;
                }
                
                $module = Module::get("RescueMe\SMS\Provider", $user_id);

                $query = "UPDATE `missing` 
                            SET `sms_sent` = NOW(), `sms_delivery` = NULL, 
                                `sms_provider` = '".DB::escape($module->impl)."',
                                `sms_provider_ref` = '".$res."'
                            WHERE `missing_id` = '" . $this->id . "';";

                if(DB::query($query) === FALSE) {
                    
                    Missing::error('Failed to update SMS status for missing ' . $this->id);
                    
                }
                
            }

            return $res;

        }// sendSMS
        
        
        /**
         * Check missing state
         * 
         * @param integer $id
         * @param boolean $admin
         * 
         * @return Missing|boolean
         */
        public static function check($id, $admin = true) {
            
            $missing = Missing::get($id, $admin);
            
            // Is check required?
            if($missing !== false) { 
                
                if(empty($missing->sms_delivery) === true 
                && empty($missing->sms_provider_ref) === false) {
                    
                    $module = Module::get("RescueMe\\SMS\\Provider", $missing->user_id);
                    $sms = $module->newInstance();
                    
                    if($missing->sms_provider === $module->impl && ($sms instanceof Check)) {
                        
                        $code = Locale::getDialCode($missing->mobile_country);
                        $code = $sms->accept($code);
                        if($sms->request($missing->sms_provider_ref,$code.$missing->mobile)) {
                            $missing = Missing::get($id);
                        }
                    }
                }
            }
            
            return $missing;
            
        }


        /**
         * Log missing location request response answered
         * 
         * @return boolean
         */
        public function answered() {

            $query = "UPDATE `missing` 
                        SET `missing_answered` = NOW() 
                      WHERE `missing_id` = '" . $this->id . "';";

            $res = DB::query($query);

            if($res === FALSE) {
                $context = array('sql' => $query);
                Missing::error('Failed to update status to ANSWERED for missing ' . $this->id, $context);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Missing {$this->id} has loaded tracking page");
            }
            return $res;
        }

        /**
         * Anonymize missing data
         * 
         * @return boolean
         */
        public function anonymize($name=MISSING_PERSON) {

            $values = prepare_values(Missing::$update, array("$name", '', ''));

            $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");
            
            if($res === FALSE) {
                Missing::error('Failed to anonymize missing ' . $this->id, $values);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Missing {$this->id} has been anonymized");
            }

            return $res;
        }


        private function getDialCode($country) {

            $code = Locale::getDialCode($country);

            if($code === FALSE) {
                $context = array('code' => $country);
                Missing::error('Failed to get country dial code', $context);
            }            
            return $code;
        }


        /**
         * Send SMS
         * 
         * @param string $country International phone number to sender
         * @param string $to Local phone number to recipient (without country dial code)
         * @param string $message Message string
         * @param boolean $missing True if recipient is missing
         * 
         * @return mixed|array Message id if success, errors otherwise (array).
         */
        private function _sendSMS($country, $to, $message, $missing) {
            
            $user_id = User::currentId();
            if(isset($user_id) === false) {
                $user_id = $this->user_id;
            }
            
            $sms = Module::get('RescueMe\SMS\Provider', $user_id)->newInstance();
            
            if($sms === FALSE)
            {
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    'Failed to get SMS provider. SMS not sent to missing' . $this->id
                );
                return false;
            }

            // facebook-copy fix (includes 3 invisible chars..)
            if(strlen($to) == 11 && (int) $to == 0) {
                $to = substr($to, 3);
            }
            
            $format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);
            
            $p = format_pos($this->last_pos, $format, false);
            
            $id = $missing ? encrypt_id($this->id) : $this->id;
            
            $message = str_replace
            (
                array('%LINK%', '#missing_id', '#to', '#m_name', '#acc', '#pos'), 
                array(LOCATE_URL,  $id, $to, $this->name, $this->last_acc, $p),
                $message
            );

            $from = Properties::get(Properties::SMS_SENDER_ID, $user_id);

            $res = $sms->send($from, $country, $to, $message);
            
            if($res) {
                
                $context = array(
                    'from' => $from,
                    'country' => $country,
                    'to' => $to,
                );
                
                $recipient = $missing ? "missing $this->id" : " to operator of $this->id";
                
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
                    'Failed to send SMS to missing ' . $this->id,
                    $context
                );
                
            }
            return $res;

        }// _sendSMS

        public function getError() {
            return DB::error();
        }
        
        
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

    }