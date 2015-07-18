<?php
    
    /**
     * File containing: Missing class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Domain;

    use \Psr\Log\LogLevel;
    use RescueMe\DB;
    use RescueMe\Locale;
    use RescueMe\Log\Logs;
    use RescueMe\Manager;
    use RescueMe\Properties;
    use RescueMe\SMS\CheckStatus;
    use RescueMe\SMS\Provider;
    use RescueMe\SMS\T;

    /**
     * Missing class
     * 
     * @package RescueMe\Domain
     */
    class Missing
    {
        const TABLE = "missing";
        
        const SELECT = 'SELECT %1$s FROM `missing`';
        
        const JOIN_OPERATION = 'LEFT JOIN `operations` ON `operations`.`op_id` = `missing`.`op_id`';

        const JOIN_HANDSET = 'LEFT JOIN `handsets` ON `handsets`.`handset_id` = `missing`.`handset_id`';

        const JOIN_SMS = 'LEFT JOIN `messages` ON `messages`.`message_id` = `missing`.`sms_id`';

        const JOIN_ACCEPT = 'LEFT JOIN `requests` ON `requests`.`request_id` = `missing`.`missing_accept_id`';

        const JOIN_USER = 'LEFT JOIN `users` ON `operations`.`user_id` = `users`.`user_id`';

        const COUNT = 'SELECT COUNT(*), `users`.`name` AS `user_name` FROM `missing`';

        private static $all = array(
            '`missing`.*',
            '`handsets`.*',
            '`messages`.*',
            '`requests`.*',
            '`missing`.`op_id`',
            '`users`.`user_id`',
            '`op_type`',
            '`op_ref`',
            '`op_closed`',
            '`alert_mobile_country`',
            '`alert_mobile`',
            '`users`.`name` AS `user_name`'
        );
        
        private static $insert = array
        (
            "missing_name", 
            "missing_mobile_country", 
            "missing_mobile", 
            "missing_locale", 
            "missing_reported",
            "op_id"
        );
        
        private static $update = array
        (
            "missing_name", 
            "missing_mobile_country", 
            "missing_mobile",
            "missing_locale",
            "sms_id",
            "sms_text"
        );

        private static $sms = array
        (
            "sms_id"
        );

        private static $sms2 = array
        (
            "sms_id",
            "sms2_sent"
        );

        private static $sms_mb = array
        (
            "sms_mb_sent"
        );

        private static $accept = array
        (
            "missing_accept_id",
            "missing_answered"
        );

        private static $position = array(
            'missing_id',
            'lat',
            'lon',
            'acc',
            'alt',
            'timestamp_device',
            'request_id'
        );


        /**
         * Missing id
         * @var integer
         */
        public $id = -1;

        /**
         * Operation id (joined with missing)
         * @var integer
         */
        public $op_id;

        /**
         * Operation reference (joined with missing)
         * @var integer
         */
        public $op_ref;

        /**
         * Handset id (joined with missing)
         * @var integer
         */
        public $handset_id;

        /**
         * User id (joined with missing)
         * @var integer
         */
        public $user_id;

        /**
         * User id (joined with missing)
         * @var integer
         */
        public $user_name;

        /**
         * Accept id (foreign key to request_id)
         * @var integer
         */
        public $accept_id;

        /**
         * Accept id (foreign key to request_id)
         * @var integer
         */
        public $answered;
        public $reported;

        public $name;
        public $type;
        public $locale = DEFAULT_LOCALE;

        public $number;
        public $number_country_code;
        
        public $alert_mobile;
        public $alert_mobile_country;

        public $last_pos;
        public $most_acc;

        public $message_sent;
        public $sms2_sent;
        public $sms_mb_sent;
        public $message_delivered;
        public $message_provider;
        public $message_reference;
        public $message_data;

        public $positions = array();
        
        public static function filter($values, $operand) {
            
            $fields = array(
                '`missing`.`missing_name`', 
                '`users`.`name`',
                '`operations`.`op_type`');

            return DB::filter($fields, $values, $operand);
            
        }

        private static function joinAll($sql) {
            return implode(' ', array(
                $sql,
                Missing::JOIN_OPERATION,
                Missing::JOIN_HANDSET,
                Missing::JOIN_SMS,
                Missing::JOIN_ACCEPT,
                Missing::JOIN_USER
            ));
        }

        private static function select($filter='', $admin = false, $start = 0, $max = false){
            
            $query = self::joinAll(sprintf(Missing::SELECT,implode(',', self::$all)));
            
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

            $query = self::joinAll(Missing::COUNT);

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
            
            $rows = array();
            while ($row = $res->fetch_assoc()) {                
                $id = $row['missing_id'];
                $missing = new Missing();
                $rows[$id] = $missing->set($id, $row);
            }
            return $rows;
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
         * @return Missing|boolean. Instance of \RescueMe\Domain\Missing is success, FALSE otherwise.
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
         * @return Missing
         */
        private function set($id, $values) {

            $this->id = (int)$id;

            foreach($values as $key => $val){
                $property = str_replace('missing_', '', $key);
                $this->$property = $val;
            }
            
            // Hack: Find out why data type is string
            $this->user_id = (int)$this->user_id;            
            
            return $this;
        }


        /**
         * Add missing to database
         *
         * @param $m_name
         * @param $m_mobile_country
         * @param $m_mobile
         * @param $m_locale
         * @param $op_id
         * @return bool|Missing
         */
        public static function add($m_name, $m_mobile_country, $m_mobile,  $m_locale, $op_id){

            if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile)
                || empty($m_locale) || empty($op_id) || empty($sms_text)) {
                
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
                
                return Missing::error("Operation $op_id does not exist, missing not added");
            }

            $values = array(
                (string) $m_name, 
                (string) $m_mobile_country, 
                (int)$m_mobile, 
                (string)$m_locale, 
                "NOW()",
                (int) $op_id
            );
            $values = prepare_values(self::$insert, $values);

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
            
            return $missing;

        }// add

        /**
         * Load missing data from database
         *
         * @param boolean $admin Administrator flag
         *
         * @return Missing|boolean. Instance of \RescueMe\Domain\Missing is success, FALSE otherwise.
         */
        public function load($admin = true){

            $res = DB::query(Missing::select('`missing_id`=' . (int) $this->id, $admin));

            if(DB::isEmpty($res)) {
                return false;
            }

            $row = $res->fetch_assoc();

            return $this->set($this->id, $row);

        }// get


        /**
         * Update missing in database
         *
         * @param $m_name
         * @param $m_mobile_country
         * @param $m_mobile
         * @param $m_locale
         * @param $sms_text
         * @return bool
         */
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


        public function getPositions() {
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
                $this->positions[$row['pos_id']] = new Position($row['pos_id']);
            }

            if(!is_array($this->positions) || count($this->positions) === 0) {
                $this->last_pos = new Position();
            }
            else {
                $this->last_pos = end($this->positions);
            }

            return $this->positions;
        }// getPositions

        /**
         * Get the most accurate position that's newer than a given minutes.
         * @param integer $maxAge How many minutes old (optional, use zero for any age)
         * @return boolean|Position
         */
        public function getMostAccurate($maxAge = 0) {
            if($this->id === -1)
                return false;

            $query = "SELECT `pos_id` FROM `positions`" .
                    " WHERE `missing_id` = " . (int) $this->id;
            if($maxAge) {
                $query .= " AND `timestamp` > NOW() - INTERVAL ".(int)$maxAge." MINUTE";
            }
            $query .= " ORDER BY `acc` LIMIT 1";

            $this->most_acc = false;

            $res = DB::query($query);

            if($res !== false) {
                $row = $res->fetch_assoc();
                $this->most_acc = new Position($row['pos_id']);
            }

            return $this->most_acc;
        }

        /**
         * @param $lat string
         * @param $lon string
         * @param $acc string
         * @param $alt string
         * @param $timestamp string
         * @param int $requestId
         * @return bool|int Position id
         */
        public function addPosition($lat, $lon, $acc, $alt, $timestamp, $requestId){

            // Sanity check
            if($this->id === -1) return false;

            // Insert new position
            $values = prepare_values(self::$position,
                array(
                    (int) $this->id,
                    (float)$lat,
                    (float)$lon,
                    (int) $acc,
                    (int) $alt,
                    format_tz($timestamp),
                    $requestId
                )
            );

            $posID = DB::insert('positions', $values);

            if($posID !== FALSE) {

                // Add to positions now
                $p = new Position($posID);

                $user_id = User::currentId();
                if(isset($user_id) === false) {
                    $user_id = $this->user_id;
                }
                $params = Properties::getAll($user_id);
                $message = 'Missing ' . $this->id . ' reported position ' . format_pos($p, $params);

                Logs::write(Logs::TRACE, LogLevel::INFO, $message );
                Logs::write(Logs::LOCATION, LogLevel::INFO, $message, $values);

                $desiredAcc = Properties::get(Properties::LOCATION_DESIRED_ACC, $this->id);
                $maxAge = Properties::get(Properties::LOCATION_MAX_AGE, $this->id);
                $most_acc_pos = $this->getMostAccurate($maxAge);
                $most_acc = $most_acc_pos ? $most_acc_pos->acc : INF;

                if(empty($this->positions)) {
                    $this->getPositions();
                }

                // Send SMS 2?
                if((int) $acc > $desiredAcc && sizeof($this->positions) > 0) {

                    // Update this object just in case
                    $this->load();

                    // Is SMS2 sent?
                    if($this->sms2_sent === 'false'){

                        // Send SMS2 to missing
                        $messageId = $this->_sendSMS(
                            $this->number_country_code,
                            $this->number,
                            T::_(T::ALERT_SMS_COARSE_LOCATION, $this->locale),
                            true);

                        // Failed to send second sms?
                        if($messageId === FALSE) {

                            $context = array(
                                'country' => $this->number_country_code,
                                'mobile' => $this->number
                            );

                            Logs::write(
                                Logs::TRACE,
                                LogLevel::ERROR,
                                sprintf(T_('Failed to send second SMS to missing %1$s'), $this->id),
                                $context
                            );

                        } else {

                            $values = prepare_values(self::$sms2, array($messageId, "true"));

                            $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");

                            if($res === FALSE){
                                Missing::error(
                                    sprintf(T_('Failed to update SMS status for missing %1$s'), $this->id), $values);
                            }

                        }
                    }
                }

                // Alert person of concern if an accurate position is logged
                // Always send first position and if the accuracy improves by 20%
                if($this->sms_mb_sent === 'false' || $acc < $most_acc * 0.8) {

                    if($this->_sendSMS(
                            $this->alert_mobile_country,
                            $this->alert_mobile,
                            T::_(T::ALERT_SMS_LOCATION_UPDATE, $this->locale), false) === FALSE) {

                        Logs::write(
                            Logs::TRACE,
                            LogLevel::ERROR,
                            sprintf(T_('Failed to send SMS with position from missing [%1$s]'), $this->id),
                            array($this->alert_mobile_country, $this->alert_mobile)
                        );


                    } else {

                        $values = prepare_values(self::$sms_mb, array("true"));

                        $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");

                        if($res === FALSE) {
                            Missing::error('Failed to update SMS alert status for missing ' . $this->id);
                        }
                    }

                }


            } else {

                Missing::error('Failed to insert position for missing ' . $this->id, $values);

            }

            return $posID;

        }// addPosition


        /**
         * Send next SMS to missing
         * @param string $text Message text
         * @return bool|int Returns message id if success, FALSE otherwise
         */
        public function sendSMS($text=null){

            if(is_null($text))
                $text = $this->message_data;

            $messageId = $this->_sendSMS($this->number_country_code, $this->number, $text, true);
            
            if($messageId === FALSE) {
                
               $this->_sendSMS(
                   $this->alert_mobile_country, 
                   $this->alert_mobile,
                   T::_(T::ALERT_SMS_NOT_SENT, $this->locale),
                   false
               );               
               
            } else {

                $values = prepare_values(self::$sms, array($messageId));

                $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");

                if($res === FALSE) {

                    Missing::error('Failed to update SMS status for missing ' . $this->id);

                }

                // Reload
                $this->load();

            }

            return $messageId;

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
                
                if(empty($missing->message_delivered) === true
                && empty($missing->message_reference) === false) {
                    
                    $module = Manager::get('RescueMe\SMS\Provider', $missing->user_id);

                    /** @var Provider $sms */
                    $sms = $module->newInstance();
                    
                    if($missing->message_provider === $module->impl && ($sms instanceof CheckStatus)) {
                        
                        $code = Locale::getDialCode($missing->number_country_code);
                        $code = $sms->accept($code);
                        /** @var CheckStatus $sms */
                        if($sms->check($missing->message_reference, $code.$missing->number)) {
                            $missing = Missing::get($id);
                        }
                    }
                }
            }
            
            return $missing;
            
        }


        /**
         * Set location request accepted state
         *
         * @param $requestId integer HTTP request id
         * @return boolean
         */
        public function accepted($requestId) {

            // TODO: Join missing with requests on accept_id and rename request_timestamp to Missing::$answered
            $values = prepare_values(self::$accept, array($requestId, 'NOW()'));

            $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");

            if($res) {
                Logs::write(
                    Logs::TRACE,
                    LogLevel::INFO,
                    "Missing {$this->id} accepted location request",
                    $values
                );
            }
            else {
                Missing::error(T_('Failed to update status to ACCEPTED for missing ') . $this->id);
            }

            return $res;
        }

        public function getAcceptRequest() {
            return Requests::get($this->accept_id);
        }

        /**
         * Anonymize missing data
         *
         * @param string|boolean $name Name
         * 
         * @return boolean
         */
        public function anonymize($name='') {

            if(!$name) {
                $name = T_('Missing person');
            }

            $values = prepare_values(Missing::$update, array("$name", '', ''));

            $res = DB::update(self::TABLE, $values, "`missing_id` = $this->id");
            
            if($res === FALSE) {
                Missing::error('Failed to anonymize missing ' . $this->id, $values);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Missing {$this->id} has been anonymized");
            }

            return $res;
        }


        /**
         * Send SMS
         * 
         * @param string $country International phone number to sender
         * @param string $to Local phone number to recipient (without country dial code)
         * @param string $message Message string
         * @param boolean $missing True if recipient is missing
         * 
         * @return integer|boolean Message id if success, boolean otherwise.
         */
        private function _sendSMS($country, $to, $message, $missing) {
            
            $userId = User::currentId();
            if(isset($userId) === false) {
                $userId = $this->user_id;
            }

            /** @var Provider $sms */
            $sms = Manager::get(Provider::TYPE, $userId)->newInstance();
            
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

            $params = Properties::getAll($userId);

            // Use most accurate position if found, last otherwise
            $p = format_pos($this->most_acc ? $this->most_acc : $this->last_pos, $params, false);
            
            $id = $missing ? encrypt_id($this->id) : $this->id;
            
            $message = str_replace
            (
                array('%LINK%', '#missing_id', '#to', '#m_name', '#acc', '#pos'), 
                array(LOCATE_URL,  $id, $to, $this->name, $this->last_pos->acc, $p),
                $message
            );

            $from = Properties::get(Properties::SMS_SENDER_ID, $userId);

            $messageId = $sms->send($from, $country, $to, $message, $userId);
            
            if($messageId) {
                
                $recipient = $missing ? "missing $this->id" : " to operator of $this->id";
                
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO,
                    "SMS sent to $recipient (message id $messageId)"
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
            return $messageId;

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
