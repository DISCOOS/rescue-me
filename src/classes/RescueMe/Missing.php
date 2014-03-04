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

    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;

    /**
     * Missing class
     * 
     * @package RescueMe
     */
    class Missing
    {
        const TABLE = "missing";

        private static $fields = array
        (
            "missing_name", 
            "missing_mobile_country", 
            "missing_mobile", 
            "missing_reported",
            "op_id",
            "sms_text"
        );

        private static $update = array
        (
            "missing_name", 
            "missing_mobile_country", 
            "missing_mobile"
        );

        public $id = -1;
        public $op_id;
        public $user_id;

        public $answered;
        public $reported;

        public $name;
        public $mobile;
        public $mobile_country;
        public $alert_mobile;

        public $last_pos;
        public $last_acc;

        public $sms2_sent;
        public $sms_mb_sent;
        public $sms_delivery;
        public $sms_provider;
        public $sms_provider_ref;

        public $positions = array();

        /**
         * Get Missing instance
         * 
         * @param integer $id Missing id
         * @param integer $phone Missing phone number (if more than one)
         * @return \RescueMe\Missing|boolean. Instance of \RescueMe\Missing is success, FALSE otherwise.
         */
        public static function getMissing($id, $phone = -1){

            $query = "SELECT * FROM `missing` WHERE `missing_id`=" . (int) $id;
            if($phone !== -1) $query .= " AND `missing_mobile`=" . (int) $phone;

            $result = DB::query($query);

            if(DB::isEmpty($result)) {
                return false;
            }

            $row = $result->fetch_assoc();

            $missing = new Missing();
            return $missing->setMissing($id, $row);

        }// getMissing


        /**
         * Set missing data from mysqli_result.
         * 
         * @param integer $id Missing id.
         * @param \mysqli_result $result Recordset.
         * 
         * @return \RescueMe\Missing
         */
        private function setMissing($id, $values) {

            $this->id = $id;

            foreach($values as $key => $val){
                $property = str_replace('missing_', '', $key);
                $this->$property = $val;
            }

            $operation = Operation::getOperation($this->op_id);
            $this->user_id = $operation->user_id;
            $this->alert_mobile = $operation->getAlertMobile();

            return $this;
        }


        public static function addMissing($m_name, $m_mobile_country, $m_mobile, $sms_text, $op_id){

            if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile) || empty($op_id) || empty($sms_text)) {
                
                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required arguments are missing", 
                    array(
                        'file' => __FILE__,
                        'method' => 'addMissing',
                        'params' => func_get_args(),
                        'line' => $line,
                    )
                );
                return false;
            }

            $sms_text = str_replace('%LINK%', SMS_LINK, $sms_text);

            $values = array(
                (string) $m_name, 
                (string) $m_mobile_country, 
                (int)$m_mobile, 
                "NOW()", 
                (int) $op_id, 
                $sms_text
            );
            $values = prepare_values(self::$fields, $values);

            $id = DB::insert(self::TABLE, $values);

            if($id === FALSE) {
                return $this->error('Failed to insert missing');
            }

            // Reuse values (optimization)
            $values = array_exclude($values,'missing_reported');            
            $missing = new Missing();
            $missing->setMissing($id, $values);
            
            Logs::write(
                Logs::TRACE, 
                LogLevel::INFO, 
                'Missing ' . $id . ' created.', 
                $values
            );
            
            return $missing->sendSMS() ? $missing : false;

        }// addMissing


        public function updateMissing($m_name, $m_mobile_country, $m_mobile){

            if(empty($m_name) || empty($m_mobile_country) || empty($m_mobile)) {
                
                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required arguments are missing", 
                    array(
                        'file' => __FILE__,
                        'method' => 'updateMissing',
                        'params' => func_get_args(),
                        'line' => $line,
                    )
                );
                return false;
            }

            $values = prepare_values(Missing::$update, array($m_name, $m_mobile_country, $m_mobile));

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
                $this->error('Failed to update missing ' . $this->id);
            }                

            return $res;

        }// updateMissing


        public function getPositions(){
            if($this->id === -1)
                return false;

            // TODO: Add sort on timestamp
            $query = "SELECT `pos_id`, `acc`, `timestamp` FROM `positions` WHERE `missing_id` = " . (int) $this->id;
            $res = DB::query($query);

            if(!$res) return false;

            $this->positions = array();
            while($row = $res->fetch_assoc()){
                $this->positions[$row['timestamp']] = new Position($row['pos_id']);
            }
            // TODO: Move to sql query
            krsort($this->positions);

            if(!is_array($this->positions) || count($this->positions) == 0) {
                $this->last_pos = new Position();
                $this->last_acc = -1;
            }
            else {
                $this->last_pos = $this->positions[key($this->positions)];
                $this->last_acc = $this->last_pos->acc;
            }

            return $this->positions;
        }// getPositions


        public function addPosition($lat, $lon, $acc, $alt, $useragent = ''){

            // Sanity check
            if($this->id === -1) return false;

            $this->last_pos = new Position();
            $this->last_pos->set(
                array(
                    'lat' => $lat,
                    'lon' => $lon,
                    'acc' => $acc,
                    'alt' => alt
                )
            );
            $this->last_acc = $acc;

            // Send SMS 2?
            if((int) $acc > 500 && sizeof($this->positions) > 1){
                
                // Update this object
                $this->getMissing($this->id);

                // Is SMS2 already sent
                if($this->sms2_sent == 'false'){

                    if($this->_sendSMS(
                        $this->mobile_country, 
                        $this->mobile, 
                        SMS2_TEXT, 
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
                            $this->error('Failed to update SMS status for missing ' . $this->id, $context);
                        }
                        
                    }
                }
            }

            // Alert person of concern if an accurate position is logged
            else if($this->sms_mb_sent == 'false') {

                if($this->_sendSMS(
                    $this->alert_mobile['country'], 
                    $this->alert_mobile['mobile'], 
                    SMS_MB_TEXT, 
                    true) === FALSE) {
                    
                    Logs::write(
                        Logs::TRACE, 
                        LogLevel::ERROR, 
                        'Failed to send SMS with position from missing ' . $this->id,
                        $this->alert_mobile
                    );
                    
                    
                } else {

                    $query = "UPDATE `missing` SET `sms_mb_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";

                    if(DB::query($query) === FALSE) {
                        $context = array('sql' => $query);
                        $this->error('Failed to update SMS status for missing ' . $this->id, $context);
                    }
                }

            }

            // Insert new position
            $values = prepare_values(array('missing_id', 'lat', 'lon', 'acc', 'alt', 'user_agent'), 
                array(
                    (int) $this->id,
                    (float)$lat,
                    (float)$lon,
                    (int) $acc,
                    (int) $alt,
                    $useragent
                )
            );
            
            $posID = DB::insert('positions', $values);
            
            if($posID !== FALSE) {               
                
                $this->positions[(int) time()] = new Position($posID);
                
                $message = 'Missing ' . $this->id . ' reported position ' . $gPoint->getNiceUTM();
                
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
                
                $this->error('Failed to insert position for missing ' . $this->id, $values);
                
            }            

        }// addPosition


        public function sendSMS(){

            $res = $this->_sendSMS($this->mobile_country, $this->mobile, $this->sms_text, true);

            if($res === FALSE) {
                
               $this->_sendSMS(
                   $this->alert_mobile['country'], 
                   $this->alert_mobile['mobile'], 
                   SMS_NOT_SENT, 
                   true
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
                    
                    $this->error('Failed to update SMS status for missing ' . $this->id);
                    
                }
                
            }

            return $res;

        }// sendSMS


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
                $this->error('Failed to update status to ANSWERED for missing ' . $this->id, $context);
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
                $this->error('Failed to anonymize missing ' . $this->id, $values);
            } else {
                Logs::write(Logs::TRACE, LogLevel::INFO, "Missing {$this->id} has been anonymized");
            }

            return $res;
        }


        private function getDialCode($country) {

            $code = Locale::getDialCode($country);

            if($code === FALSE) {
                $context = array('code' => $country);
                $this->error('Failed to get country dial code', $context);                
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
            
            $sms = Module::get("RescueMe\SMS\Provider", $user_id)->newInstance();
            
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
            
            $position = format_pos($this->last_pos, $format);

            $message = str_replace
            (
                array('#missing_id', '#to', '#m_name', '#acc', '#pos'), 
                array($this->id, $to, $this->name, $this->last_acc, $position),
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
        
        
        private function error($message, $context = array())
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