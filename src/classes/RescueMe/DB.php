<?php

    /**
     * File containing: Database class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 13. June 2013
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
         * Get default DB instance
         * 
         * @return DB 
         */
        private static function instance()
        {
            if(!isset(DB::$instance))
            {
                DB::$instance = new DB();
            }
            if(!isset(DB::$instance->mysqli))
            {
                DB::$instance->connect();
            }
            return DB::$instance;
        }// instance
        
        
        /**
         * Connect to database.
         * 
         * @param string $host DB host
         * @param string $usr DB username
         * @param string $pwd DB password
         * @param string $name DB name
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function connect($host=DB_HOST, $usr = DB_USERNAME, $pwd = DB_PASSWORD, $name=DB_NAME)
        {
            if(!isset($this->mysqli))
            {
                $this->mysqli = mysqli_connect($host, $usr, $pwd);
                $this->mysqli->query("SET NAMES 'utf8'");
            }
            else if($this->mysqli->connect_error)
            {
                $this->mysqli->init()->real_connect($host, $usr, $pwd);
                $this->mysqli->query("SET NAMES 'utf8'");
            }
            return $this->database($name);
        }// connect
        
        
        /**
         * Use database.
         * 
         * @param string $name DB name
         * 
         * @return TRUE if success, FALSE otherwise.
         */
        public function database($name=DB_NAME)
        {
            if(isset($this->mysqli) && !$this->mysqli->connect_error)
            {
                return $this->mysqli->select_db($name);
            }
            return false;
        }// database
        
        
        /**
         * Performs a query on the RescueMe database.
         * 
         * @param string $sql SQL query.
         * 
         * @return mixed FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries 
         * mysqli_query will return a mysqli_result object. For successfull INSERT queries with 
         * AUTO_INCREMENT field, the auto generated id is returned. For other successful queries 
         * the method will return TRUE.
         * 
         * @throws \Exception If not connected.
         */
        public static function query($sql)
        {
            if(DB::instance()->mysqli->connect_error)
            {
                $code = mysqli_connect_errno(DB::instance()->mysqli);
                $error = mysqli_connect_error(DB::instance()->mysqli);
                throw new Exception("Failed to connect to MySQL: " . $error, $code);
            }// if
            
            $result = DB::instance()->mysqli->query($sql);
            if($result == true && strpos($sql, "INSERT") !== false) {
                return DB::instance()->mysqli->insert_id;
            }
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
           return DB::instance()->mysqli->escape_string($string);
        }// escape
        
        
        /**
         * Returns the error code for the most recent function call.
         * 
         * @return integer An error code value for the last call, if it failed. zero means no error occurred.
         */
        public static function errno()
        {
            return DB::instance()->mysqli->errno;
        }// errno
        
        
        /**
         * Returns a string description of the last error.
         * 
         * @return string A string that describes the error. An empty string if no error occurred.
         */
        public static function error()
        {
            return DB::instance()->mysqli->error;
        }// error
        
        
        public static function prepare($format, $parameter, $_ = null)
        {
            $args = array_slice(func_get_args(),1);
            $params = array($format);
            foreach($args as $arg) {
                $params[] = is_string($arg) && !($arg === "NULL" || is_function($arg)) ? DB::escape($arg) : $arg;
            }
            return call_user_func_array("sprintf",  $params);
        }


        /**
         * Get selection from given table
         * 
         * @param string $table
         * @param mixed $fields
         * @param string $filter
         * @param string $order
         * @return boolean|\mysqli_result
         */
        public static function select($table, $fields="*", $filter="", $order="") 
        {
            if(is_string($fields) && $fields !== "*") {
                $fields = "`" . ltrim(rtrim($fields,"`"),"`") . "`";
            }
            elseif (is_array($fields)) {
                $fields = "`" . implode("`,`", $fields) . "`";
            }
            
            $query = "SELECT $fields FROM `$table`";
            
            if($filter) $query .= " WHERE $filter";
            
            if($order) $query .= " ORDER BY $order";
            
            return DB::query($query);
            
        }// select
        
        
        /**
         * Insert values into given table.
         * 
         * @param string $table
         * @param array $values
         * @return integer|boolean FALSE on failure, integer if table has AUTO_INCREMENT primary id, TRUE otherswise.
         */
        public static function insert($table, $values)
        {
            $fields = "`" . implode("`,`", array_keys($values)) . "`";
            $inserts = array();
            foreach($values as $value) {
                if(is_string($value) && !($value === "NULL" || is_function($value)))
                    $value = "'" . DB::escape($value) . "'";
                $inserts[] = $value;
            }
            
            $query = "INSERT INTO `$table` ($fields) VALUES (". implode(",", $inserts) . ")";
            
            return DB::query($query);
            
        }// insert
        
        
        /**
         * Delete rows from given table.
         * 
         * @param string $table
         * @param string $filter
         * 
         * @return boolean TRUE on success, FALSE otherswise.
         */
        public static function delete($table, $filter='')
        {
            $query = "DELETE FROM `$table`";
            
            if($filter) $query .= " WHERE $filter";            
            
            return DB::query($query);
            
        }// delete        
        
        
        /**
         * Update table with given values.
         * 
         * @param string $table
         * @param array $values
         * @param string $filter
         * @return boolean
         */
        public static function update($table, $values, $filter) 
        {
            $query = "UPDATE `$table` SET ";
            $updates = array();
            foreach($values as $field =>$value) {
                if(is_string($value)  && !($value === "NULL" || is_function($value))) 
                    $value = "'" . DB::escape($value) . "'";
                $updates[] = "$field=$value";
            }
            $query .= implode(",", $updates);
            if($filter) $query .= " WHERE $filter";
            
            return DB::query($query);
            
        }// update
        
        
        /**
         * Check if database exists.
         * 
         * @param string $name Database name
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function exists($name)
        {
            $mysqli = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
            if($mysqli->connect_error)
            {
                $code = mysqli_connect_errno($this->mysqli);
                $error = mysqli_connect_error($this->mysqli);
                throw new Exception("Failed to connect to MySQL: " . $error, $code);
            }// if
            $result = $mysqli->select_db($name);
            unset($mysqli);
            return $result;
        }// exists
        
        
        /**
         * Create database with given name.
         * 
         * @param string $name Database name
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function create($name)
        {
            $mysqli = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
            if($mysqli->connect_error)
            {
                $code = mysqli_connect_errno($this->mysqli);
                $error = mysqli_connect_error($this->mysqli);
                throw new Exception("Failed to connect to MySQL: " . $error, $code);
            }// if
            $result = $mysqli->select_db($name);
            if($result === FALSE)
            {
                $sql = "CREATE DATABASE IF NOT EXISTS $name";
                $result = $mysqli->query($sql) && $mysqli->select_db($name);
            }
            unset($mysqli);
            return $result;
        }// create
        
        
        /**
         * Import SQL dump into database.
         * 
         * @param string $pathname Path to file
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function import($pathname)
        {
            $skipped = array();
            $executed = array();
            $previous = array('INSERT');
            $clauses = array('INSERT', 'UPDATE', 'DELETE', 'DROP', 'GRANT', 'REVOKE', 'CREATE', 'ALTER');
            if(file_exists($pathname))
            {
                $query = '';
                $queries = array();
                $lines = file($pathname);
                if(is_array($lines))
                {
                    foreach($lines as $line)
                    {
                        $line = trim($line);
                        if(!preg_match("#^--|^/\*#", $line))
                        {
                            if(!trim($line))
                            {
                                if($query != '')
                                {
                                    $clause = trim(strtoupper(substr($query, 0, strpos($query, ' '))));
                                    if(in_array($clause, $clauses))
                                    {
                                        $pos = strpos($query, '`') + 1;
                                        $query = substr($query, 0, $pos) . substr($query, $pos);
                                    }

                                    $priority = 1;
                                    if(in_array($clause, $previous))
                                    {
                                        $priority = 10;
                                    }
                                    $queries[$priority][] = $query;
                                    $query = '';
                                }
                            }
                            else
                            {
                                $query .= $line;
                            }
                        }
                    }
                    ksort($queries);
                    foreach($queries as $sqls)
                    {
                        foreach($sqls as $sql)
                        {
                            // Check if table exists
                            $skip = false;
                            if(strpos($sql, "CREATE TABLE") === 0) 
                            {
                                $table = DB::table($sql);
                                if(($skip = DB::query("DESCRIBE `$table`")) !== false) 
                                {
                                    $skipped[] = $sql;
                                }
                            }
                            if(DB::query($sql) === false)
                            {
                                return false;
                            }
                            if(!$skip) $executed[] = $sql;
                        }
                    }
                    // Was tables skipped?
                    if(!empty($skipped)) {
                        // Add missing columns
                        if(($altered = DB::alter($skipped)) === false) {
                            return false;
                        }
                        $executed = array_merge($executed, $altered);
                    }
                }
            }
            
            return $executed;
        }// import
        
        
        private static function alter($skipped)
        {
            $executed=array();
            foreach($skipped as $create)
            {
                $exists = array();
                $table = DB::table($create);
                $result = DB::query("SHOW COLUMNS FROM `$table`;");
                if($result !== false)
                {
                    while($row = $result->fetch_row())
                    {
                        $exists[] = $row[0];
                    }
                    $columns = DB::columns($create, $table);
                    foreach(explode(",", $columns) as $sql)
                    {
                        if(strpos($sql, "`") === 0)
                        {
                            $sql = rtrim($sql, ",");
                            $column = DB::column($sql);
                            if(!in_array($column, $exists))
                            {
                                $query = "ALTER TABLE `$table` ADD COLUMN $sql;";
                                if(DB::query($query) === false)
                                {
                                    return false;
                                }
                                $executed[] = $query;
                            }
                        }
                    }
                }
            }
            return $executed;
        }

        private static function table($query)
        {
            $table = array();
            preg_match("#CREATE TABLE IF NOT EXISTS `([a-z_]*)`#i", $query, $table);
            return $table[1];
        }
        
        
        private static function columns($query, $table)
        {
            $columns = array();
            preg_match("#CREATE TABLE IF NOT EXISTS `$table` \((.*)\)#i", $query, $columns);
            return $columns[1];
        }
        
        
        private static function column($query)
        {
            $column = array();
            preg_match("#`.*`#", $query, $column);
            return trim($column[0], "`");
        }
        
        
       /**
         * Export SQL dump from database.
         * 
         * @param string $pathname Path to export file
         * @param string $charset Default table charset 
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function export($pathname, $charset="utf8")
        {
            if(file_exists($pathname)){
                unlink($pathname);
            }
            $tables = DB::query("SHOW TABLES");
            if(DB::isEmpty($tables)) return false;

            $lines = '';
            while ($row = $tables->fetch_row()) {
                $result = DB::query("SHOW CREATE TABLE `$row[0]`");
                if(DB::isEmpty($result)) return false;
                $table = $result->fetch_assoc();
                $lines .= "-- --------------------------------------------------------\n\n";
                $lines .= "-- \n";    
                $lines .= "-- Structure for table `$row[0]`\n";
                $lines .= "-- \n\n";    
                $create = preg_replace("#CREATE TABLE#i", "CREATE TABLE IF NOT EXISTS", $table['Create Table']);
                $create = preg_replace("# AUTO_INCREMENT=[0-9]+#i", "", $create);
                $create = preg_replace("#CHARSET=.+#i", "CHARSET=$charset", $create);
                $lines .= "$create;\n\n";
            }
            file_put_contents($pathname, $lines);
            return $lines;
        }// export


    }// DB
