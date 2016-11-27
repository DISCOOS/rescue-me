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

    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;


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
        public static function instance()
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
        public function connect($host = null, $usr = null, $pwd = null, $name = null)
        {        
            // Use defaults?
            $host = $host ? $host : (defined('DB_HOST') ? DB_HOST : "");
            $usr = $usr ? $usr : (defined('DB_USERNAME') ? DB_USERNAME : "");
            $pwd = $pwd ? $pwd : (defined('DB_PASSWORD') ? DB_PASSWORD : "");
            $name = $name ? $name : (defined('DB_NAME') ? DB_NAME : "");
            
            // Sanity check
            if(!($host && $name)) {
                return false;
            }
            
            // Connect to database
            if(!isset($this->mysqli))
            {
                $this->mysqli = mysqli_connect($host, $usr, $pwd);
                DB::configure($this->mysqli);
            }
            else if($this->mysqli->connect_error)
            {
                $this->mysqli->init()->real_connect($host, $usr, $pwd);
                DB::configure($this->mysqli);
            }

            
            return $this->database($name);
            
        }// connect


        /**
         * Use database.
         *
         * @param mixed|string $name DB name
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
         * @throws \Exception
         * @return mixed FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries
         * mysqli_query will return a mysqli_result object. For successfull INSERT queries with
         * AUTO_INCREMENT field, the auto generated id is returned. For other successful queries
         * the method will return TRUE.
         *
         */
        public static function query($sql)
        {
            if(DB::instance()->mysqli->connect_error)
            {
                $code = mysqli_connect_errno(DB::instance()->mysqli);
                $error = mysqli_connect_error(DB::instance()->mysqli);
                throw new \Exception("Failed to connect to MySQL: " . $error, $code);
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
            return isset($res) === FALSE || $res === FALSE || 
                ($res instanceof \mysqli_result) && mysqli_num_rows($res) === 0;
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
           return DB::instance()->mysqli->real_escape_string($string);
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
         * Get row count
         * 
         * @param string $table
         * @param string $filter
         * @return boolean|integer
         */
        public static function count($table, $filter='') 
        {
            $query = "SELECT COUNT(*) FROM `$table`";
            
            if($filter) $query .= " WHERE $filter";
            
            $res = DB::query($query);
            
            if(DB::isEmpty($res)) return false;
            
            $row = $res->fetch_row();

            return (int)$row[0];
            
        }// count
        

        /**
         * Get selection from given table
         * 
         * @param string $table
         * @param mixed $fields
         * @param string $filter
         * @param string $order
         * @param string $limit
         * @return boolean|\mysqli_result
         */
        public static function select($table, $fields='*', $filter='', $order='', $limit = '') 
        {
            if(is_string($fields) && in_array(strtoupper($fields), array('*','COUNT(*)')) === FALSE) {
                $fields = "`" . ltrim(rtrim($fields,"`"),"`") . "`";
            }
            elseif (is_array($fields)) {
                $fields = "`" . implode("`,`", $fields) . "`";
            }
            
            $query = "SELECT $fields FROM `$table`";
            
            if($filter) $query .= " WHERE $filter";
            
            if($order) $query .= " ORDER BY $order";
            
            if($limit) $query .= " LIMIT $limit";
            
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
                if(is_string($value) && ($value === "NULL" || is_function($value)) === FALSE) {
                    $value = '"'.DB::escape($value).'"';
                }
                $inserts[] = $value;
            }
            
            $query = "INSERT INTO `$table` ($fields) VALUES (". implode(",", $inserts) . ")";
            
            $res = DB::query($query);
            
            if($table !== 'logs')
            {
                if($res === FALSE)
                {
                    $context['query'] = $query;
                    $context['error'] = DB::error();
                    Logs::write(Logs::DB, LogLevel::ERROR, 'Failed to insert ' . count($values) . " values into $table", $context);
                } else {
                    Logs::write(Logs::DB, LogLevel::INFO, 'Inserted ' . count($values) . " values into $table");
                }
            }
            
            return $res;
            
        }// insert
        
        
        /**
         * Delete rows from given table.
         * 
         * @param string $table
         * @param string $filter
         * 
         * @return boolean TRUE on success, FALSE otherwise.
         */
        public static function delete($table, $filter='')
        {
            $count = DB::count($table, $filter);
            
            $query = "DELETE FROM `$table`";
            
            if($filter) $query .= " WHERE $filter";
            
            $res = DB::query($query);
            
            if($res === FALSE)
            {
                Logs::write(Logs::DB, LogLevel::ERROR, "Failed to delete $count rows from $table", DB::error());
            } else {
                Logs::write(Logs::DB, LogLevel::INFO, "Deleted $count rows from $table");
            }
            
            return true;
            
        }// delete        
        
        
        /**
         * Update table with given values.
         * 
         * @param string $table
         * @param array $values
         * @param string $filter
         * @return boolean TRUE on success, FALSE otherswise.
         */
        public static function update($table, $values, $filter) 
        {
            $query = "UPDATE `$table` SET ";
            $updates = array();
            foreach($values as $field =>$value) {
                if(is_string($value) && !($value === "NULL" || is_function($value))) {
                    $value = "'" . DB::escape($value) . "'";
                }
                $updates[] = "`$field`=$value";
            }
            $query .= implode(",", $updates);
            if($filter) $query .= " WHERE $filter";
            
            $res = DB::query($query);
            
            if($res === FALSE)
            {
                Logs::write(Logs::DB, LogLevel::ERROR, 'Failed to update ' . count($values) . " values in $table", DB::error());
            } else {
                Logs::write(Logs::DB, LogLevel::INFO, 'Updated ' . count($values) . " values in $table");
            }
            
            return $res;
            
        }// update


        /**
         * Check if database exists.
         *
         * @param string $name Database name
         *
         * @throws \Exception
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function exists($name)
        {
            $mysqli = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
            if($mysqli->connect_error)
            {
                $code = mysqli_connect_errno($mysqli);
                $error = mysqli_connect_error($mysqli);
                throw new \Exception("Failed to connect to MySQL: " . $error, $code);
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
         * @throws \Exception
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function create($name)
        {
            $mysqli = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
            if($mysqli->connect_error)
            {
                $code = mysqli_connect_errno($mysqli);
                $error = mysqli_connect_error($mysqli);
                throw new \Exception("Failed to connect to MySQL: " . $error, $code);
            }// if
            $res = $mysqli->select_db($name);
            if($res === FALSE)
            {
                $sql = "CREATE DATABASE IF NOT EXISTS $name";
                $res = $mysqli->query($sql) && $mysqli->select_db($name);
            }
            unset($mysqli);
            
            return $res;
        }// create
        
        
        /**
         * Build string matching filter from fields and values
         * @param array|mixed $fields Fully qualified table names
         * @param array|mixed $values Values to match
         * @param string $operand Operand between each predicate
         * @return string
         */
        public static function filter($fields, $values, $operand) {
            
            $fields = is_array($fields) ? $fields : array($fields);
            $values = is_array($values) ? $values : array($values);
            
            $filter = '';
            $columns = count($fields) === 1 ? reset($fields) : 'CONCAT_WS('. implode(',',$fields) . ')';

            foreach($values as $value) {
                $filter[] = "$columns LIKE '%{$value}%'";
            }
            
            if(empty($filter) === false) {
                $filter = '(' . implode(") $operand (", $filter) . ')';
            }
            
            return $filter;
        }
        
        
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
                        if(!preg_match("#^--|^/\\*#", $line))
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
                        if(($altered = DB::alter($skipped))) {
                            $executed = array_merge($executed, $altered);
                        }
                    }
                }
            }
            
            if(empty($executed) === FALSE)
            {
                $count = count($executed);
                Logs::write(Logs::DB, LogLevel::INFO, "Imported $pathname ($count sentences executed).", $executed);
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
                    
                    foreach($columns as $sql)
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
            
            if(empty($executed) === FALSE)
            {
                $count = count($executed);
                Logs::write(Logs::DB, LogLevel::INFO, "Altered $count database entities.", $executed);
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
            $body = array();
            preg_match("#CREATE TABLE IF NOT EXISTS `$table` \\((.*)\\)#i", $query, $body);
            $column = "";
            $columns = array();
            foreach(explode(",", $body[1]) as $item) {
                // Next column found?
                if(strpos($item, "`") === 0 && $column) {
                    $columns[] = $column;
                    $column = $item;
                } 
                // Key found?
                else if(stripos($item, "primary") === 0 || stripos($item, "key") === 0) {
                    $columns[] = $column;
                    $column = "";
                }
                else {
                    // Implode enum values
                    $column = "$column, $item";
                }
            }
            
            // Add trailing column? (no keys found)
            if($column) $columns[] = $column;
            
            // Finished
            return $columns;
        }
        
        
        private static function column($query)
        {
            $column = array();
            preg_match("#`.*`#", $query, $column);
            return trim($column[0], "`");
        }
        
        /**
         * Configure database connection.
         * <ol>
         * <li>Connection encoding to 'utf8'</li>
         * <li>Connection timesone offset from php timesone</li>
         * </ol>
         * @param \mysqli $mysqli
         * @return boolean TRUE if success, FALSE otherwise
         */
        private static function configure($mysqli) {
            
            // Enforce 'utf8' encoding
            if($mysqli->query("SET NAMES 'utf8'") === FALSE) {
                return false;
            }
            
            //
            // Enforce current timezone
            // 
            // (see http://www.sitepoint.com/synchronize-php-mysql-timezone-configuration)
            //
            $offset = TimeZone::getOffset();

            if($mysqli->query("SET time_zone='$offset';") === FALSE) {
                return false;
            }
            
            return true;
            
        }


        /**
         * Reconfigure MySQL connection parameters:
         * <ul>
         * <li>timezone</li>
         * </ul>
         *
         * @return boolean
         *
         */
        public static function reconfigure() {
            //
            // Enforce current timezone
            //
            // (see http://www.sitepoint.com/synchronize-php-mysql-timezone-configuration)
            //
            $offset = $offset = TimeZone::getOffset();

            return DB::query("SET time_zone='$offset';");

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
            $count = 0;
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
                $count++;
            }
            
            file_put_contents($pathname, $lines);
            
            $name = (defined('DB_NAME') ? DB_NAME : "");
            
            Logs::write(Logs::DB, LogLevel::INFO, "Exported $count tables from $name", $pathname);
            
            return $lines;
        }// export


    }// DB
