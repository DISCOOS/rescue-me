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

    use Closure;
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
         * Check if database is an legacy version that requires migration
         */
        public static function legacyVersion() {
            return DB::latestVersion() === false;
        }


        /**
         * Get latest database version
         * @return bool|string
         */
        public static function latestVersion() {

            $res = self::select('versions', 'version_name', '', 'version_id DESC', '1');
            if(self::isEmpty($res)) {
                return false;
            }
            $row = $res->fetch_row();
            return $row[0];
        }

        /**
         * Get db version history
         * @return bool|array
         */
        public static function versionHistory() {
            $res = self::select(
                'versions',
                array('version_name','version_date')
            );
            if(self::isEmpty($res)) {
                return false;
            }
            return $res->fetch_all(MYSQLI_ASSOC);

        }

        /**
         * Set db version
         * @param string $name
         * @return bool|int
         */
        public static function setVersion($name) {
            if($res = DB::insert('versions', array('version_name' => $name))) {

            }
            return $res;
        }



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
            $host = $host ? $host : self::host();
            $usr = $usr ? $usr : self::username();
            $pwd = $pwd ? $pwd : self::password();
            $name = $name ? $name : self::name();
            
            // Sanity check
            if(!$host) {
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

            return $name ? $this->database($name) : true;
            
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
         * @throws DBException
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
                throw new DBException("Failed to connect to MySQL: " . $error, $code);
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
         * @return integer|boolean FALSE on failure, integer if table has AUTO_INCREMENT primary id, TRUE otherwise.
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
                $context['query'] = $query;
                if($res === FALSE)
                {
                    $context['error'] = DB::error();
                    Logs::write(Logs::DB, LogLevel::ERROR, 'Failed to insert ' . count($values) . " values into $table", $context);
                } else {
                    Logs::write(Logs::DB, LogLevel::INFO, 'Inserted ' . count($values) . " values into $table", $context);
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

        public static function host() {
            return (defined('DB_HOST') ? DB_HOST : "");
        }

        public static function username() {
            return (defined('DB_USERNAME') ? DB_USERNAME : "");
        }
        public static function password() {
            return (defined('DB_PASSWORD') ? DB_PASSWORD : "");
        }
        public static function name() {
            return (defined('DB_NAME') ? DB_NAME : "");
        }

        /**
         * Check if database exists.
         *
         * @param string $name Database name
         *
         * @throws DBException
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function exists($name)
        {
            $mysqli = DB::instance()->mysqli;
            if($mysqli->connect_error)
            {
                $code = mysqli_connect_errno($mysqli);
                $error = mysqli_connect_error($mysqli);
                throw new DBException("Failed to connect to MySQL: " . $error, $code);
            }// if
            $result = $mysqli->select_db($name);
            unset($mysqli);
            return $result;
        }// exists


        /**
         * Create database with given name.
         *
         * @param string $name Database name
         * @param bool $use Use database after creation
         *
         * @throws DBException
         *
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public function create($name, $use = true)
        {
            $mysqli = DB::instance()->mysqli;
            if(DB::instance()->mysqli->connect_error)
            {
                $code = mysqli_connect_errno($mysqli);
                $error = mysqli_connect_error($mysqli);
                throw new DBException("Failed to connect to MySQL: " . $error, $code);
            }// if

            if($use) {
                $res = $mysqli->select_db($name);
                if($res === FALSE)
                {
                    $sql = "CREATE DATABASE IF NOT EXISTS $name";
                    $res = $mysqli->query($sql) && $mysqli->select_db($name);
                }
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
         * Source queries into database.
         *
         * @param array $queries Data to source into database
         *
         * @throws DBException
         *
         * @return int Number of sources queries.
         */
        public function source($queries)
        {
            $executed = array();

            // Disable auto-commit, store current state
            $flag = DB::instance()->mysqli->autocommit(false);

            try {
                foreach($queries as $sql)
                {
                    if(DB::query($sql) === false)
                    {
                        $msg = sprintf("Query [$sql] failed. %s (code %s)", self::error(), self::errno());
                        throw new DBException($msg, DB::errno());
                    }
                    $executed[] = $sql;
                }

                // Commit changes
                if(FALSE === DB::instance()->mysqli->commit()) {
                    throw new DBException(DB::error(), DB::errno());
                }

            } catch (DBException $e) {
                if(DB::instance()->mysqli->rollback()) {
                    $exception = DBException::asFatal("Failed to source queries.", $e);
                } else {
                    $exception = DBException::asFatal("Failed to rollback queries.", $e);
                }
            }

            // Restore previous state
            DB::instance()->mysqli->autocommit($flag);

            if(!isset($exception)) {
                return count($executed);
            } else {
                throw $exception;
            }

        }

        /**
         * @param array $lines Lines to fetch queries from
         * @param callable $prepare Called before each query is added to result
         * @return array Identified queries
         */
        public function fetch_queries($lines, Closure $prepare = null) {
            $query = '';
            $queries = array();
            if(is_array($lines))
            {
                foreach($lines as $line)
                {
                    $line = trim($line);

                    // Skip until first legal character in query
                    if(!preg_match("#^--|^/\\*#", $line)) {
                        $query .= ' ' . $line;
                        if(endsWith($line,';')) {
                            $queries[] = is_callable($prepare) ? $prepare(trim($query)) : trim($query);
                            $query = '';
                        }
                    }
                }
                if(!empty($query)) {
                    $queries[] = is_callable($prepare) ? $prepare(trim($query)) : trim($query);
                }
            }
            return $queries;
        }



        /**
         * Source SQL into database.
         * 
         * @param string $pathname Path to file
         * 
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function source_old($pathname)
        {
            $success = true;
            $executed = array();

            // Determine if database is versioned (if not, attempt to append missing columns)
            $versioned = DB::latestVersion();

            // Disable auto-commit, store current state
            $flag = DB::instance()->mysqli->autocommit(false);


            try {
                $skipped = array();
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
                                    if($query !== '')
                                    {
                                        $queries[] = $query;
                                        $query = '';
                                    }
                                }
                                else
                                {
                                    $query .= $line;
                                }
                            }
                        }
                        if(!empty($query)) {
                            $queries[] = $query;
                        }
                        foreach($queries as $sql)
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
                            if(!$skip) {
                                if(DB::query($sql) === false)
                                {
                                    $msg = sprintf("Query [$sql] failed. %s (code %s)", self::error(), self::errno());
                                    throw new \Exception($msg, self::errno());
                                }
                                $executed[] = $sql;
                            }
                        }

                        // Add add missing columns in skipped 'CREATE TABLE' queries?
                        if($versioned === false && !empty($skipped)) {
                            if(($altered = DB::alter($skipped))) {
                                $executed = array_merge($executed, $altered);
                            }
                        }
                    }
                }

                // Commit changes
                if(FALSE === DB::instance()->mysqli->commit()) {
                    Logs::write(Logs::DB, LogLevel::ERROR, "Failed to source $pathname.");
                }

            } catch (\Exception $e) {
                $rollback = DB::instance()->mysqli->rollback();
                Logs::write(Logs::DB, LogLevel::ERROR, "Failed to source $pathname.", Logs::toArray($e));
                if($rollback === false) {
                    Logs::write(Logs::DB, LogLevel::ERROR, "Failed to rollback $pathname.");
                }
                $success = false;
            }

            // Restore previous state
            DB::instance()->mysqli->autocommit($flag);

            $count = count($executed);
            Logs::write(Logs::DB, LogLevel::INFO, "Sourced $pathname ($count sentences executed).", $executed);

            return $success;
        }// source
        
        
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
         * Export database structure SQL.
         *
         * @param string $pathname Path to export file
         * @param string $generator Generator name
         * @param string $charset Default table charset
         *
         * @return boolean TRUE if success, FALSE otherwise.
         */
        public static function export($pathname, $generator, $charset="utf8")
        {
            // TODO: Fix export that create tables in order that ensures all foreign keys exists before constraints
            $count = 0;
            if(file_exists($pathname)){
                unlink($pathname);
            }
            $tables = DB::query("SHOW TABLES");
            if(DB::isEmpty($tables)) return false;

            // Export header
            $lines  = sprintf('-- MySQL Script generated by %s', $generator);
            $lines .= sprintf('-- %s', date('c'));
            $lines .= sprintf('-- Model: %s', 'RescueMe');
            $lines .= sprintf('-- Version: %s', DB::latestVersion());
            $lines .= "\n\n";

            // Export database structure
            while ($row = $tables->fetch_row()) {
                $result = DB::query("SHOW CREATE TABLE `$row[0]`");
                if(DB::isEmpty($result)) return false;
                $table = $result->fetch_assoc();
                $lines .= "-- --------------------------------------------------------\n\n";
                $lines .= "-- \n";    
                $lines .= "-- Structure for table `$row[0]`\n";
                $lines .= "-- \n\n";    
                $create = preg_replace("#CREATE TABLE#i", "CREATE TABLE IF NOT EXISTS", $table['Create Table']);
                $create = preg_replace("#AUTO_INCREMENT\\s*=[0-9]+#i", "AUTO_INCREMENT = 1", $create);
                $create = preg_replace("#CHARSET\\s*=[.^\\s]+#i", "CHARSET = $charset", $create);
                $create = preg_replace("#DEFAULT CHARACTER SET\\s*=[.^\\s]+#i", "DEFAULT CHARACTER SET = $charset", $create);
                $lines .= "$create;\n\n";
                $count++;
            }
            
            file_put_contents($pathname, $lines);
            
            $name = (defined('DB_NAME') ? DB_NAME : "");
            
            Logs::write(Logs::DB, LogLevel::INFO, "Exported $count tables from $name", $pathname);
            
            return $lines;
        }// export


    }// DB
