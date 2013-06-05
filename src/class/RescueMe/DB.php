<?php

    /**
     * File containing: Database class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 13. June 2013, v. 1.00
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe;

    /**
     * Database class
     * 
     * @package RescueMe
     */
    final class DB
    {
        /**
         * DB instance
         * 
         * @var DB
         */
        private static $instance;
        
        /**
         * Connection instance
         * 
         * @var \mysqli
         */
        private $mysqli;
        
        /**
         * Constructor
         *
         * @since 15. June 2013, v. 7.60
         *
         */
        public function __construct()
        {
            $this->mysqli = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            if(($code = mysqli_connect_errno($this->mysqli)) > 0)
            {
                throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error($this->mysqli), $code);
            }// if
        }// __construct
        
        
        /**
         * Get default DB instance
         * 
         * @return DB 
         */
        private static function instance()
        {
            if(!isset(self::$instance))
            {
                self::$instance = new DB();
            }
            return self::$instance;
        }// instance
        
        
        /**
         * Performs a query on the RescueMe database.
         * 
         * @param string $sql SQL query.
         * 
         * @return mixed FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries 
         * mysqli_query will return a mysqli_result object. For successfull INSERT queries with 
         * AUTO_INCREMENT field, the auto generated id is returned. For other successful queries 
         * the method will return TRUE.
         */
        public static function query($sql)
        {
            $result = self::instance()->mysqli->query($sql);
            if($result == true && self::instance()->mysqli->insert_id > 0)
                return self::instance()->mysqli->insert_id;
            return $result;
        }// query
        
        
        /**
         * Check if result set is empty.
         * 
         * @param \mysqli_result $res Result set
         * 
         * @return FALSE if result set is FALSE or empty, TRUE otherwise.
         */
        public static function isEmpty($res) 
        {
            return !($res && mysqli_num_rows($res));
        }// isEmpty
        
        
        /**
         * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection.
         * 
         * @param string $string Any string
         * 
         * @return string Returns an escaped string.
         */
        public static function escape($string)
        {
           return self::instance()->mysqli->escape_string($string);
        }// escape
        
        
        /**
         * Returns the error code for the most recent function call.
         * 
         * @return integer An error code value for the last call, if it failed. zero means no error occurred.
         */
        public static function errno()
        {
            return $this->instance()->mysqli->errno;
        }// errno
        
        
        /**
         * Returns a string description of the last error.
         * 
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public static function error()
        {
            return $this->instance()->mysqli->error;
        }// error
        
        
        public static function prepareINSERT($table, $fields, $values) 
        {
            return "INSERT INTO `$table` ($fields) VALUES ($values)";
        }// prepareINSERT
        
        
    }// DB
