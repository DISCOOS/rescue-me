<?php

    /**
     * File containing: Missing class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 13. June 2013, v. 1.00
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    namespace RescueMe;
    
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
            "missed_by_name", 
            "missed_by_email", 
            "missed_by_mobile", 
            "missing_name", 
            "missing_mobile", 
            "missing_reported"
        );
            
        public $id = -1;
        public $positions = array();
        public $mb_name;
        public $mb_email;
        public $mb_mobile;
        public $m_name;
        public $m_mobile;
        public $timestamp_sms_sent;

        /**
         * Get Missing instance
         * 
         * @param integer $id Missing id
         * @param integer $phone Missing phone number (if more than one)
         * @return mixed. Instance of \RescueMe\Missing is success, FALSE otherwise.
         */
        public static function getMissing($id, $phone = -1){
            $missing = new Missing();
            $missing->id = $id;

            $query = "SELECT * FROM `missing` WHERE `missing_id`=" . (int) $missing->id;
            if($phone !== -1) $query .= " AND `missing_mobile`=" . (int) $phone;
            $res = DB::query($query);

            if(DB::isEmpty($res)) return false;

            $row = $res->fetch_assoc();
            foreach($row as $key => $val){
                $property = str_replace(array('missing_', 'missed_by_'), array('m_', 'mb_'), $key);
                $missing->$property = $val;
            }
            
            return $missing;
            
        }// getMissing


        public function addMissing($mb_name, $mb_email, $mb_mobile, $m_name, $m_mobile){
            
            if(empty($mb_name) || empty($mb_email) || empty($mb_mobile) || empty($m_name) || empty($m_mobile))
                return false;
            
            $values = array((string) $mb_name,  (string) $mb_email,  (int) $mb_mobile,  (string) $m_name,  (int) $m_mobile, "NOW()");
            
            $values = prepare_values(self::$fields, $values);
            
            $id = DB::insert(self::TABLE, $values);
                
            if(!$id) return false;

            $missing = self::getMissing($id);
            
            return ($missing->sendSMS() == true);
            
        }// addMissing
        
        
        public static function getAllMissing($status='open') {
            
            // Get WHERE clause
            switch( $status ) {
                case 'open': 		
                    $where = "!= 'Closed'";		
                    break;
                case 'closed':		
                    $where = "= 'Closed'";		
                    break;
                default:
                    $where = ' NOT NULL';
            }
            
            $query = "SELECT `missing_id`, `missing_name` FROM `missing` WHERE `status` {$where} ORDER BY `missing_reported` DESC";
            $res = DB::query($query);

            if (DB::isEmpty($res)) return false;

            $missing_ids = array();
            while ($row = $res->fetch_assoc()) {
                $missing = self::getMissing($row['missing_id']);
                $missing_ids[$row['missing_id']] = $missing;
            }
            return $missing_ids;
        }
        

        public function getPositions(){
            if($this->id === -1)
                return false;

            $query = "SELECT `pos_id`, `acc`, `timestamp` FROM `positions` WHERE `missing_id` = " . (int) $this->id;
            $res = DB::query($query);

            if(!$res) return false;

            $this->positions = array();
            while($row = $res->fetch_assoc()){
                $this->positions[(int) $row['timestamp']] = new Position($row['pos_id']);
            }
            krsort($this->positions);

            if(!is_array($this->positions) || count($this->positions) == 0) {
                $this->last_pos = new Position();
            }
            else {
                $this->last_pos = $this->positions[key($this->positions)];
            }

            return $this->positions;
        }// getPositions


        public function addPosition($lat, $lon, $acc, $alt, $timestamp, $useragent = ''){
            
            // Sanity check
            if($this->id === -1) return false;

            // Send SMS 2?
            if((int) $acc > 500){
                
                // Update this object
                $this->getMissing($this->id);
                
                // Is SMS2 alreadt sent?
                if($this->sms2_sent == 'false'){
                    
                    $this->_sendSMS($this->m_mobile, SMS2_TEXT);
                    $query = "UPDATE `missing` SET `sms2_sent` = 'true' WHERE `missing_id` = '" . $this->id . "';";
                    $res = DB::query($query);
                    if(!$res){
                        trigger_error("Failed execute [$query]: " . DB::error(), E_USER_WARNING);
                    }// if
                }
            }
            
            // Insert new position
            $query = "INSERT INTO `positions` (`missing_id`, `lat`, `lon`, `acc`, `alt`, `timestamp`, `user_agent`) VALUES (" . 
                (int) $this->id . ", " . DB::escape($lat) . ", " . DB::escape($lon) . ", " .
                (int) $acc . ", " . (int) $alt . ", " . (int) $timestamp . ", '" . DB::escape($useragent) . "')";

            $posID = DB::query($query);

            if(!$posID) return false;

            $this->positions[(int) $timestamp] = new Position($posID);
            
        }// addPosition


        public function sendSMS(){
            
            $query = "UPDATE `missing` SET `sms_sent` = NOW() WHERE `missing_id` = '" . $this->id . "';";
            if(!DB::query($query)) {
                trigger_error("Failed execute [$query]: ".DB::error(), E_USER_WARNING);
            }
            
            $res = $this->_sendSMS($this->m_mobile, SMS_TEXT);
            if(!$res) {
                $res = $this->_sendSMS($this->mb_mobile, SMS_NOT_SENT);
            }
            
            return $res;
            
        }// sendSMS


        private function _sendSMS($to, $message) {

            ## Facebook-copy fix (includes 3 invisible chars..)
            if(strlen($to) == 11 && (int) $to == 0)
                $to = substr($to, 3);

            // Create message
            $message = urlencode
            (
                str_replace
                (
                    array('#missing_id', '#mb_name'), array($this->id . '-' . $to, $this->mb_name), $message
                )
            );
            
            $module = Module::get("SMS");
            
            $sms = $module->newInstance();
            
            if(!$sms)
            {
                echo "Failed!";
                return false;
            }

            return $sms->send($to, SMS_FROM, $message);
            
        }// _sendSMS


    }// Missing

