<?php
    /**
     * File containing: Operation class
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
    use RescueMe\Missing;
    
    /**
     * Operation class
     * 
     * @package RescueMe
     */
    class Operation {

        const TABLE = "operations";

        private static $fields = array
        (
            "user_id", 
            "op_name", 
            "alert_mobile_country",
            "alert_mobile",
            "op_ref", 
            "op_opened", 
            "op_closed",
            "op_comments"
        );
        
        const OPEN = 'open';
        const CLOSED = 'closed';

        public $id = -1;
        public $user_id = -1;

        /**
         * Get Operation instance
         * 
         * @param integer $id Operation id
         * @return mixed. Instance of \RescueMe\Operation if success, FALSE otherwise.
         */
        public static function get($id){
            $query = "SELECT * FROM `".self::TABLE."` WHERE `op_id`=" . (int) $id;
            $res = DB::query($query);

            if(DB::isEmpty($res)) 
                return false;

            $operation = new Operation();
            $operation->id = $id;

            $row = $res->fetch_assoc();
            foreach($row as $key => $val){
                $operation->$key = $val;
            }

            return $operation;
        }// get


        /**
         * Check i given operation is closed
         * 
         * @param integer $id Operation id
         * @return boolean TRUE if closed (or not found), FALSE otherwise.
         */
        public static function isClosed($id) {

            $query = "SELECT op_closed FROM `".self::TABLE."` WHERE `op_id`=" . (int) $id;
            $res = DB::query($query);

            if(DB::isEmpty($res)) 
                return false;

            $row = $res->fetch_row();

            return isset($row[0]) && !empty($row[0]);        
        }



        /**
         * Close given operation
         * 
         * @param integer $id Operation id
         * @param array $update Operation values
         * 
         * @return boolean
         */
        public static function close($id, $update = array()) {
            
            // Anonymize operation
            if (isset($update['op_name']) === FALSE) {
                $op_name = date('Y-m-d');
            } else {
                $op_name = $update['op_name'];
            }
            
            // Overwrite existing values
            $update = array_merge(
                $update, 
                prepare_values(
                    array('op_closed','op_name'), 
                    array('NOW()', $op_name)
                )
            );
            
            // Limit to legal values
            $values = array();
            foreach(self::$fields as $field) {
                if(isset($update[$field])) {
                    $values[$field] = $update[$field];
                }
            }
                

            // Close operation
            $res = DB::update(self::TABLE, $values, "`op_id`=" . (int) $id);

            if($res === FALSE) {
                return $this->error("Failed to close operation $id");
            }

            Logs::write(
                Logs::TRACE, 
                LogLevel::INFO, 
                "Operation $id closed"
            );        

            return true;

        }

        /**
         * Update a field in the DB
         * @param int $id Operation ID
         * @param string $field DB-field to update
         * @param string $value New valye
         * @return boolean
         */
        public static function set($id, $field, $value) {

            $res = DB::update(self::TABLE,array($field => $value), "`op_id`=" . (int) $id);

            if($res === FALSE) {
                return $this->error("Failed to update field [$field] in operation $id");
            }

            return $res;
        }


        /**
         * Reopen given operation
         * 
         * @param integer $id Operation id
         * @return boolean
         */
        public static function reopen($id) {

            $res = DB::update(self::TABLE,array('op_closed' => 'NULL'), "`op_id`=" . (int) $id);

            if($res === FALSE) {
                $this->error("Failed to reopen operation $id");
            } else {

                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO, 
                    "Operation $id reopened"
                );                
            }

            return $res;

        }
        
        
        public function getData() {
            return array
            (
                "op_id" => (int) $this->op_id, 
                "user_id" => (int) $this->user_id, 
                "op_name" => $this->op_name, 
                "alert_mobile_country" => $this->alert_mobile_country,
                "alert_mobile" => $this->alert_mobile,
                "op_ref" => $this->op_ref, 
                "op_opened" => $this->op_opened, 
                "op_closed" => $this->op_closed,
                "op_comments" => $this->op_comments
            );
        }        


        /**
         * Add a new operation
         * 
         * @param string $op_name Operation name
         * @param int $user_id User ID of the "owner" (Tip: often $_SESSION['user_id'])
         * @param string $alert_mobile_country Country code (ISO)
         * @param string $alert_mobile Mobilephone to alert of recieced positions, etc
         * @param string $op_ref Reference of the operation, like SAR-number or something
         * @param string $op_comments Any comments to the operation
         * @return boolean
         */
        public function add(
            $op_name, $user_id, $alert_mobile_country, 
            $alert_mobile, $op_ref = '', $op_comments = ''){

            if(empty($op_name) || empty($user_id) || empty($alert_mobile_country) || empty($alert_mobile)) {

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
                (int) $user_id, 
                (string) $op_name, 
                (string) $alert_mobile_country, 
                (string) $alert_mobile, 
                (string) $op_ref, 
                "NOW()", 
                (string) $op_comments
            );

            $values = array_exclude(prepare_values(self::$fields, $values),'op_closed');
            $this->id = DB::insert(self::TABLE, $values);

            if($this->id === FALSE) {
                $this->error("Failed to create operation", $values);
            } else {

                Logs::write(
                    Logs::TRACE, 
                    LogLevel::INFO, 
                    "Operation {$this->id} created"
                );                
            }        

            return self::get($this->id);

        }// add

        /**
         * Get all operations
         * 
         * @param string $status NULL, 'open' or 'closed'
         * @return mixed. Instance of \RescueMe\Operation if success, FALSE otherwise.
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

            $query = "SELECT `op_id`, `op_name` FROM `".self::TABLE."`
                      WHERE `op_closed` {$where} {$owned} ORDER BY `op_opened` DESC";

            $res = DB::query($query);

            if (DB::isEmpty($res))
                return false;

            $operation_ids = array();
            while ($row = $res->fetch_assoc()) {
                $operation = new Operation();
                $operation = $operation->get($row['op_id']);
                $operation_ids[$row['op_id']] = $operation;
            }

            return $operation_ids;

        } // getAll
        

        public function getAllMissing($admin = false, $start = 0, $max = false) {
            return Missing::getAll('`missing`.`op_id` = ' .(int)$this->id, $admin, $start, $max);
        }

        public function getAlertMobile() {
            if (empty($this->alert_mobile))
                return false;

            return array('country'=>$this->alert_mobile_country, 
                        'mobile'=>$this->alert_mobile);
        }

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



    }// Operation