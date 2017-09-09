<?php
    /**
     * File containing: Trace class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 22. July 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    namespace RescueMe;

    use Psr\Log\LogLevel;
    use RescueMe\Log\Logs;

    /**
     * Trace class
     *
     * @parameter $trace_id
     * @parameter $trace_type
     * @parameter $trace_name
     * @parameter $user_id
     * @parameter $trace_ref
     * @parameter $trace_opened
     * @parameter $trace_closed
     * @parameter $trace_comments
     * @parameter $trace_alert_country
     * @parameter $trace_alert_number
     *
     * @package RescueMe
     */
    class Trace {

        const TABLE = "traces";

        private static $fields = array
        (
            "trace_type",
            "trace_name",
            "user_id", 
            "trace_alert_country",
            "trace_alert_number",
            "trace_ref",
            "trace_opened",
            "trace_closed",
            "trace_comments"
        );
        
        const TRACE = 'trace';
        
        const TEST = 'test';
        
        const EXERCISE = 'exercise';
        
        
        const OPEN = 'open';
        const CLOSED = 'closed';

        public $id = -1;
        public $user_id = -1;
        
        public static function titles() {
            return array('trace' => T_('Trace'), 'test' => T_('Test'), 'exercise' => T_('Exercise'));
        }

        /**
         * Get Trace instance
         * 
         * @param integer $id Trace id
         * @return Trace|bool Instance of \RescueMe\Trace if success, FALSE otherwise.
         */
        public static function get($id){
            $query = "SELECT * FROM `".self::TABLE."` WHERE `trace_id`=" . (int) $id;
            $res = DB::query($query);

            if(DB::isEmpty($res)) 
                return false;

            $trace = new Trace();
            $trace->id = $id;

            $row = $res->fetch_assoc();
            foreach($row as $key => $val){
                $trace->$key = $val;
            }

            return $trace;
        }// get


        /**
         * Check i given trace is closed
         * 
         * @param integer $id Trace id
         * @return boolean TRUE if closed (or not found), FALSE otherwise.
         */
        public static function isClosed($id) {

            $query = "SELECT trace_closed FROM `".self::TABLE."` WHERE `trace_id`=" . (int) $id;
            $res = DB::query($query);

            if(DB::isEmpty($res)) 
                return false;

            $row = $res->fetch_row();

            return isset($row[0]) && !empty($row[0]);        
        }



        /**
         * Close given trace
         * 
         * @param integer $id Trace id
         * @param array $update Trace values
         * 
         * @return boolean
         */
        public static function close($id, $update = array()) {
            
            // Anonymize trace
            if (isset($update['trace_name']) === FALSE) {
                $trace_name = date('Y-m-d');
            } else {
                $trace_name = $update['trace_name'];
            }
            
            // Overwrite existing values
            $update = array_merge(
                $update, 
                prepare_values(
                    array('trace_closed','trace_name'),
                    array('NOW()', $trace_name)
                )
            );
            
            // Limit to legal values
            $values = array();
            foreach(self::$fields as $field) {
                if(isset($update[$field])) {
                    $values[$field] = $update[$field];
                }
            }
                

            // Close trace
            $res = DB::update(self::TABLE, $values, "`trace_id`=" . (int) $id);

            if($res === FALSE) {
                return Trace::error("Failed to close trace $id");
            }

            Logs::write(
                Logs::TRACE, 
                LogLevel::INFO, 
                "Trace $id closed"
            );        

            return true;

        }

        /**
         * Update a field in the DB
         * @param int $id Trace ID
         * @param string $field DB-field to update
         * @param string $value New valye
         * @return boolean
         */
        public static function set($id, $field, $value) {

            $res = DB::update(self::TABLE,array($field => $value), "`trace_id`=" . (int) $id);

            if($res === FALSE) {
                return Trace::error("Failed to update field [$field] in trace $id");
            }

            return $res;
        }


        /**
         * Reopen given trace
         * 
         * @param integer $id Trace id
         * @return boolean
         */
        public static function reopen($id) {

            $res = DB::update(self::TABLE,array('trace_closed' => 'NULL'), "`trace_id`=" . (int) $id);

            if($res === FALSE) {
                Trace::error("Failed to reopen trace $id");
            } else {

                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO, 
                    "Trace $id reopened"
                );                
            }

            return $res;

        }
        
        
        public function getData() {
            return array
            (
                "trace_id" => (int) $this->trace_id,
                "user_id" => (int) $this->user_id, 
                "trace_name" => $this->trace_name,
                "trace_alert_country" => $this->trace_alert_country,
                "trace_alert_number" => $this->trace_alert_number,
                "trace_ref" => $this->trace_ref,
                "trace_opened" => $this->trace_opened,
                "trace_closed" => $this->trace_closed,
                "trace_comments" => $this->trace_comments
            );
        }        


        /**
         * Add a new trace
         * 
         * @param string $trace_type Trace type
         * @param string $trace_name Trace name
         * @param int $user_id User ID of the "owner" (Tip: often $_SESSION['user_id'])
         * @param string $trace_alert_country Country code (ISO)
         * @param string $trace_alert_number phone to alert of received positions, etc
         * @param string $trace_ref Reference of the trace, like SAR-number or something
         * @param string $trace_comments Any comments to the trace
         * @return boolean
         */
        public static function add(
            $trace_type, $trace_name, $user_id, $trace_alert_country,
            $trace_alert_number, $trace_ref = '', $trace_comments = ''){

            if(empty($trace_type) || empty($trace_name) || empty($user_id)
                || empty($trace_alert_country) || empty($trace_alert_number)) {

                $line = __LINE__;
                Logs::write(
                    Logs::TRACE, 
                    LogLevel::ERROR, 
                    "One or more required values are missing",
                    array(
                        'file' => __FILE__,
                        'method' => 'add',
                        'line' => $line,
                    )
                );
                return false;
            }

            $values = array(
                (string) $trace_type,
                (string) $trace_name,
                (int) $user_id, 
                (string) $trace_alert_country,
                (string) $trace_alert_number,
                (string) $trace_ref,
                "NOW()",
                "NULL",
                (string) $trace_comments
            );
            
            $values = array_exclude(prepare_values(self::$fields, $values),'trace_closed');
            $id = DB::insert(self::TABLE, $values);
            
            if($id === FALSE) {
                self::error("Failed to create trace", $values);
            } else {

                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO, 
                    "Trace {$id} created"
                );                
            }        

            return self::get($id);

        }// add

        /**
         * Get all trace
         *
         * @param string $status NULL, 'open' or 'closed'
         * @param bool $admin
         * @return mixed. Instance of \RescueMe\Trace if success, FALSE otherwise.
         */
        public static function getAll($status='open', $admin = false) {
            $user = User::current();
            // Get WHERE clause
            switch( $status ) {
                case 'open': 		
                    $where = " IS NULL";		
                    break;
                case 'closed':		
                default:
                    $where = " IS NOT NULL";
                    break;
            }
            
            $owned = ($admin ? '' : "AND `".self::TABLE."`.`user_id` = ".(int)$user->id);

            $query = "SELECT `trace_id`, `trace_name` FROM `".self::TABLE."`
                      WHERE `trace_closed` {$where} {$owned} ORDER BY `trace_opened` DESC";

            $res = DB::query($query);

            if (DB::isEmpty($res))
                return false;

            $traces_ids = array();
            while ($row = $res->fetch_assoc()) {
                $trace = new Trace();
                $trace = $trace->get($row['trace_id']);
                $traces_ids[$row['trace_id']] = $trace;
            }

            return $traces_ids;

        } // getAll
        

        public function getAllMobiles($admin = false, $start = 0, $max = false) {
            return Mobile::getAll('`mobiles`.`trace_id` = ' .(int)$this->id, $admin, $start, $max);
        }

        public function getAlertMobile() {
            if (empty($this->trace_alert_number))
                return false;

            return array('country'=>$this->trace_alert_country,
                        'mobile'=>$this->trace_alert_number);
        }

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



    }// Trace