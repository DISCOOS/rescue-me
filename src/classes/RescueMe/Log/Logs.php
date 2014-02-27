<?php
    
    /**
     * File containing: Logs class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 15. June 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    namespace RescueMe\Log;
    
    use \RescueMe\DB;
    use \RescueMe\User;

    /**
     * Logs class
     * 
     * @package RescueMe
     * 
     * @property integer $id Logs id
     * @property string $name Full name
     * @property string $email Email address
     * @property string $mobile Mobile number
     */
    class Logs {
        
        const TABLE = "logs";
        
        const SELECT = 'SELECT logs.*,users.name as user FROM `logs` LEFT JOIN `users` ON `users`.`user_id` = `logs`.`user_id`';
        
        private static $fields = array
        (
            "name", 
            "level", 
            "message", 
            "context", 
            "user_id", 
            "client_ip"
        );
        
        /**
         * All logs
         */
        const ALL = "all";
        
        
        /**
         * Access log
         */
        const ACCESS = "access";
        
        
        /**
         * System log
         */
        const SYSTEM = "system";
        
        
        /**
         * Database log
         */        
        const DB = "db";
        
        
        /**
         * SMS provider log
         */        
        const SMS = "sms";
        
        
        /**
         * Trace log
         */        
        const TRACE = "trace";
        
        
        /**
         * Location log
         */        
        const LOCATION = "location";
        
        
        /**
         * Array of logs
         */
        public static $all = array(
            self::ACCESS,
            self::TRACE,
            self::LOCATION,
            self::SYSTEM,
            self::DB,
            self::SMS
        );
        
        
        /**
         * Get log titles
         * @return array
         */
        public static function getTitles() {
            return array(
                Logs::ALL => _('All'),
                Logs::TRACE => _('Trace'),
                Logs::LOCATION => _('Locations'),
                Logs::SMS => _('SMS'),
                Logs::ACCESS => _('Access'),
                Logs::DB =>  _('Database'),
                Logs::SYSTEM => _('System'),
            );            
        }        
        
        /**
         * Get log tile
         * @param string $log Log name
         * @return string
         */
        public static function getTitle($log) {
            $titles = Logs::getTitles();            
            return isset($titles[$log]) ? $titles[$log] : false;
        }        
        
        
        /**
         * Get all logs in database
         * 
         * @param array $logs Logs (optional, default: null - all)
         * 
         * @return array|boolean
         */
        public static function getAll($logs=null) {
            
            if(isset($logs) === FALSE || in_array(Logs::ALL, $logs)) {
                $logs = Logs::$all;
            }
            
            foreach($logs as $name) {
                $filter[] = "`logs`.`name`='$name'";
            } 
            $filter = implode($filter,' OR ');
            
            $res = DB::query(Logs::SELECT . ' WHERE ' . $filter . ' ORDER BY `date` DESC;');
            
            if (DB::isEmpty($res)) return false;

            $logs = array();
            while ($row = $res->fetch_assoc()) {
                $logs[$row['log_id']] = $row;
            }
            return $logs;
            
        }// getAll     
        
        
        /**
         * Get log with given name
         * 
         * @param string $name Log name
         * 
         * @return array|boolean
         */
        public static function get($name) {
            
            if($name === Logs::ALL) {
                return Logs::getAll();
            }
            
            $res = DB::query(Logs::SELECT . " WHERE `logs`.`name`='" . $name . "' ORDER BY `date` DESC");

            if (DB::isEmpty($res)) return false;
            
            $logs = array();
            while ($log = $res->fetch_assoc()) {
                $logs[$log['log_id']] = $log;
            }
            return $logs;
            
        }// get
        
        
        
        /**
         * Write message to log.
         *
         * @param string $level Log level
         * @param string $message Message text
         * @param array $context Log context values
         * @param integer $user_id User id
         *
         * @return void
         */
        public static function write($name, $level, $message, $context = array(), $user_id = null) {
            
            // Ensure user id
            if(isset($user_id) === false) {
                $user_id = User::currentId();
            }
            if(isset($user_id) === false) {
                $user_id = 0;
            }
            
            $ip = get_client_ip();
            $context = empty($context) ? '' : utf8_encode(json_encode($context));            
            $values = array($name, $level, $message, $context, $user_id, $ip);
            
            $values = prepare_values(self::$fields, $values);
            
            if(DB::insert(self::TABLE, $values) === false) {
                
                trigger_error(DB::escape(DB::error()), E_USER_WARNING);
                
            }
        }
        
        
    }// Logs
