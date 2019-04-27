<?php
    
    /**
     * File containing: User class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 15. June 2013
     * 
     * @author Sven-Ove Bjerkan <post@sven-ove.no>
     */

    namespace RescueMe;
    
    use \Psr\Log\LogLevel;
    use ReflectionException;
    use \RescueMe\Log\Logs;
    use RescueMe\SMS\Provider;

    /**
     * User class
     * 
     * @package RescueMe
     * 
     * @property integer $id User id
     * @property string $name Full name
     * @property string $email Email address
     * @property string $mobile Mobile number
     */
    class User {
        
        const TABLE = "users";
        
        private static $insert = array
        (
            "name", 
            "password", 
            "email", 
            "mobile",
            "mobile_country",
            "state"
        );
        
        private static $update = array
        (
            "name", 
            "email", 
            "mobile",
            "mobile_country"
        );
        
        
        /**
         * All users
         */
        const ALL = "all";
        
        
        /**
         * Active users
         */
        const ACTIVE = 'active';
        
        
        /**
         * Disabled users
         */
        const DISABLED = 'disabled';
        
        
        /**
         * Pending users
         */
        const PENDING = 'pending';
        
        
        /**
         * Deleted users
         */
        const DELETED = 'deleted';
        
        
        /**
         * Array of user states
         */
        public static $all = array(
            self::ACTIVE,
            self::DISABLED,
            self::PENDING,
            self::DELETED
        );
        
        /**
         * User id
         * @var integer
         */
        public $id;
        
        
        /**
         * Full name
         * @var string
         */
        public $name;
        
        
        /**
         * User email (username)
         * @var string
         */
        public $email;
        
        
        /**
         * User mobile number
         * @var string
         */
        public $mobile;
        
        
        /**
         * User mobile number country 
         * @var string
         */
        public $mobile_country;
        
        /**
         * Role granted user.
         * @var integer
         */
        public $role_id = null;

        /**
         * User created.
         * @var string
         */
        public $created = null;
        
        
        /**
         * User state
         * @var integer
         */
        public $state = null;
        
        
        /**
         * Prevent initialization of user object outside this class
         */
        protected final function __construct()
        {
            
        }


        /**
         * Check if one or more admins exist
         *
         * @return boolean
         * @throws DBException
         */
        public static function isEmpty() {
            
            $res = DB::count(User::TABLE);
            
            return $res !== FALSE && $res == 0;
            
        }// isEmpty


        /**
         * Get user state titles
         * @return array
         */
        public static function getTitles() {
            return array(
                User::ACTIVE => T_('Active'),
                User::PENDING => T_('Pending'),
                User::DISABLED => T_('Disabled'),
                User::DELETED => T_('Deleted'),
                User::ALL => T_('All')
            );            
        }        
        
        
        public static function filter($values, $operand) {
            
            $fields = array(
                '`users`.`name`', 
                '`users`.`email`', 
                '`users`.`mobile`');

            return DB::filter($fields, $values, $operand);
            
        }


        /**
         * Count number of users
         * @param array $states User state (optional, default: null, values: {'pending', 'disabled', 'deleted'})
         * @param string $filter
         * @return boolean|array
         * @throws DBException
         */
        public static function count($states=null, $filter = '') {
            
            if(isset($states) === FALSE || in_array(User::ALL, $states)) {
                $states = User::$all;
            }
            
            $where = array();
            foreach(isset($states) ? $states : array() as $state) {
                $where[] = $state === null || $state === "NULL"  ? "`state` IS NULL" : "`state`='$state'";
            } 
            if(empty($where) === false) {
                if (empty($filter) === false) {
                    $filter = '(' . $filter . ') AND ';
                }
                $filter .= implode($where," OR ");
            }
            
            return DB::count(self::TABLE, $filter);
            
        }// count


        /**
         * Get all users in database
         * @param string|array $states User state (optional, default: null, values: {'pending', 'disabled', 'deleted'})
         * @param string $filter
         * @param int $start
         * @param bool $max
         * @return boolean|array
         * @throws DBException
         */
        public static function getAll($states = null, $filter = '', $start = 0, $max = false) {

            if(isset($states) && is_string($states)) {
                $states = array($states);
            }

            if(isset($states) === FALSE || in_array(User::ALL, $states)) {
                $states = User::$all;
            }
            
            $where = array();
            foreach(isset($states) ? $states : array() as $state) {
                $where[] = $state === null || $state === "NULL"  ? "`state` IS NULL" : "`state`='$state'";
            }

            if(empty($where) === false) {

                $where = '(' . implode($where," OR ") . ')';

                $filter =  empty($filter) ? $where : '(' . $filter . ') AND ' . $where;

            }
            
            $limit = ($max === false ? '' : "$start, $max");

            $res = DB::select(self::TABLE, "*", $filter, "`state`, `name`", $limit);
            
            if (DB::isEmpty($res)) return false;

            $users = array();
            while ($row = $res->fetch_assoc()) {
                $user = self::get($row['user_id']);
                $users[$row['user_id']] = $user;
            }
            return $users;
            
        }// getAll     
        
        
        /**
         * Get current user id.
         * 
         * @return integer|null.
         */
        public static function currentId() {
            return (int)isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        }


        /**
         * Get current user.
         *
         * @return boolean|User User instance if found, FALSE otherwise.
         * @throws DBException
         */
        public static function current() {
            return isset($_SESSION['user_id']) ? User::get($_SESSION['user_id']) : false;
        }


        /**
         * Get user id from SMS provider reference id
         *
         * @param $provider
         * @param integer $reference
         *
         * @return integer|boolean Trace id if success, FALSE otherwise.
         * @throws DBException
         */
        public static function getProviderUserId($provider, $reference) {
            
            
            // Get all mobile with given reference
            $select = "SELECT `trace_id` FROM `mobiles` WHERE `sms_provider` = '".$provider."' AND `sms_provider_ref` = '".$reference."';";

            $result = DB::query($select);
            if(DB::isEmpty($result)) {                 
                return User::error("No user id found. $provider reference $reference not found.");                
            }
            $row = $result->fetch_row();
            $trace = Trace::get($row[0]);
            return $trace ? $trace->user_id : false;
        }


        /**
         * Get user with given id
         *
         * @param integer $id User id
         * @param null $user
         * @return boolean|User
         * @throws DBException
         */
        public static function get($id, $user = null) {
            
            $res = DB::select(self::TABLE,'*', "`user_id` = ".(int)$id);
            
            if (DB::isEmpty($res)) return false;
            
            $exclude = array("user_id", 'password');

            if($user === null) {
                $user = new User();
            }
            $row = $res->fetch_assoc();
            foreach($row as $property => $value){
                
                if(in_array($property, $exclude) === false) { 
                    $user->$property = $value;
                }
            }
            
            $user->id = (int)$id;
            $res = DB::select('roles', 'role_id', "`user_id` = ".(int)$id);
            if(DB::isEmpty($res) === FALSE) {
                $row = $res->fetch_array();
                $user->role_id = (int)$row[0];
            }
            
            return $user;
            
        }// get


        /**
         * Recover user
         *
         * @param string $email
         * @param array $methods
         *
         * @return boolean
         * @throws DBException
         * @throws ReflectionException
         */
        public static function recover($email, $methods = array()) {
            
            $filter = "`email` = '$email'";
            
            $res = DB::select(self::TABLE,"user_id", $filter);

            if(DB::isEmpty($res)) 
            {
                return User::error(T_('User not found').' '.T_('Recovery password not sent'), func_get_args());
            }
            
            $row = $res->fetch_row();
            
            $user = self::get($row[0]);
            
            $password = $user->reset();
            
            $message = $password."\n".sprintf(T_('Your single-use %1$s password'), TITLE);
            
            $res = $user->send($message, $methods);
            
            if($res !== false) {
                return User::log(sprintf(T_('Recovery password sent to user %1$s'), $row[0]));
            } 
            
            return User::error(sprintf(T_('Failed to send recovery password to user %1$s'),$row[0]), func_get_args());
            
            
        }// recover


        /**
         * Create new user
         *
         * @param string $name
         * @param string $email
         * @param string $hash
         * @param string $country
         * @param string $mobile
         * @param integer $role
         * @param string $state
         * @return User|boolean
         * @throws DBException
         * @internal param string $hash
         */
        public static function create($name, $email, $hash, $country, $mobile, $role, $state = User::ACTIVE) {

            $user = false;

            $username = User::safe(strtolower($email));

            if(empty($username) || empty($hash) || User::unique($email) === false) {
                return false;
            }
            
            $values = array((string) $name, (string) $hash, (string) $username, (int) $mobile, (string) $country, $state);
            
            $values = \prepare_values(User::$insert, $values);
            
            $res = false;
            
            if(($id = DB::insert(self::TABLE, $values)) !== false) {
                $user = User::get($id);
                $res = Roles::grant($role, $user->id);
            }
            
            if($res !== false) {
                return User::log(sprintf(T_('User %1$s is created.'), $user->id), LogLevel::INFO, $user);
            } 
            
            return User::error(T_('Failed to create user'), func_get_args());
            
            
        }// create
        

        /**
         * Check if one or more users exist
         *
         * @param string $state State name
         * 
         * @return boolean
         */
        public function isState($state) {
            
            return $this->state === $state;
            
        }// isEmpty


        /**
         * Update user
         *
         * @param string $name
         * @param string $email
         * @param string $country
         * @param string $mobile
         * @param mixed $role id or name
         * @return boolean
         * @throws DBException
         */
        public function update($name, $email, $country, $mobile, $role = null) {
            
            $res = false;
            
            $username = User::safe(strtolower($email));
            
            if(empty($username) === FALSE)
            {
                $changed = $username !== strtolower(User::safe($this->email));
                
                // Ensure unique email
                if($changed === FALSE || User::unique($email)) {

                    $values = array((string)$name, (string)$email, (int)$mobile, (string)$country);

                    $values = prepare_values(User::$update, $values);

                    if(DB::update(self::TABLE, $values, "user_id=$this->id")) {

                        $res = true; 
                        if($role !== null) {
                            $res = Roles::grant($role, $this->id);
                        }
                    }
                }
            }
            
            if($res !== false) {
                return User::log("User {$this->id} updated");
            } 
            
            return User::error("Failed to update user {$this->id}", func_get_args());
            
        }// update


        /**
         * Reset user password.
         *
         * Returns random password
         *
         * @param integer $length Length of random password (default: PASSWORD_LENGTH)
         *
         * @return string|boolean
         * @throws DBException
         */
        public function reset($length = PASSWORD_LENGTH) {
            
            $password = self::generate($length);

            return $this->password($password) ? $password : false;
            
        }// reset


        /**
         * Set user password.
         *
         * @param string $tokens Password
         *
         * @return boolean
         * @throws DBException
         */
        public function password($tokens) {

            $hash = User::hash(trim($tokens));
            
            $values = \prepare_values(array("password"), array($hash));
            
            $result = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($result !== false && isset_get($_SESSION,'user_id') == $this->id) {
                $_SESSION['password'] = $hash;
            }
            
            if($result !== false) {
                return User::log("User {$this->id} password changed");
            } 
            
            return User::error("Failed to change user {$this->id} password", $hash);
            
        }// password


        /**
         * Delete user.
         *
         * @return boolean
         * @throws DBException
         */
        public function delete() {
            
            $values = \prepare_values(array("state"), array(User::DELETED));
            
            $res = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($res !== false) {
                return User::log("User $this->id deleted");
            }
            
            return User::error("Failed to delete user $this->id", $values);
            
        }// delete        


        /**
         * Disable user.
         *
         * @return boolean
         * @throws DBException
         */
        public function disable() {
            
            $values = \prepare_values(array("state"), array(User::DISABLED));
            
            $res = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($res !== false) {
                return User::log("User $this->id disabled");
            }
            
            return User::error("Failed to disable user $this->id", $values);
            
        }// disable        


        /**
         * Enable user.
         *
         * @return boolean
         * @throws DBException
         */
        public function enable() {
            
            $values = \prepare_values(array("state"), array(User::ACTIVE));
            
            $res = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($res !== false) {
                return User::log("User $this->id enabled");
            }
            
            return User::error("Failed to enable user $this->id");
            
        }// disable     

        /**
         * Approve a pending user.
         * Will alert the user by mail and SMS.
         *
         * @return boolean
         * @throws DBException
         * @throws ReflectionException
         */
        public function approve() {
            if ($this->enable()) {
                $message = sprintf(T_('Your %1$s account is approved'), TITLE). ' ' .
                    sprintf(T_('Log in to %1$s'), APP_URL);
                $this->send($message, array('sms', 'email'));
                return true;
            }
            return false;
        }

        /**
         * Reject a pending user.
         * Will alert the user by mail and SMS.
         *
         * @return boolean
         * @throws DBException
         * @throws ReflectionException
         */
        public function reject() {
            if ($this->delete()) {
                $this->send(sprintf(T_('Your %1$s account request is rejected'), TITLE),
                    array('sms', 'email'));
                return true;
            }
            return false;
        }

        /**
         * Send message to user devices
         *
         * @param string $message The text to send
         * @param array $devices Send to given devices {'sms','email'};
         *
         * @return boolean
         * @throws DBException
         * @throws ReflectionException
         */
        public function send($message, $devices = array()) {

            $sent = 0;
            
            $devices = array_map(function($device) { return strtolower($device);}, $devices);
            
            $devices = array_intersect(array('sms','email'), $devices);
            
            $all = empty($devices);
            
            if($all || in_array('sms', $devices)) {

                /** @var Provider $sms */
                $sms = Manager::get(Provider::TYPE, User::currentId())->newInstance();
                if(!$sms)
                {
                    return User::error(T_('Failed to get SMS provider'));
                }

                $res = $sms->send($this->mobile_country, $this->mobile, $message, null, function () {
                    return sprintf(T_('Failed to send SMS to user %s'), $this->id);
                });

                if($res !== FALSE) {
                    $sent++;
                }
            }
                
            return $sent > 0;

        }// send


        /**
         * Attempt to login in user
         *
         * @param string $email
         * @param string $password
         * @return boolean
         * @throws DBException
         */
        public function logon($email, $password) {

            $username = User::safe(strtolower($email));

            $hash = User::hash($password);

            if(empty($username) || empty($hash))
                return false;
            
            $filter = "`email` = '$username' AND `password` = '$hash'";
            
            $res = DB::select(self::TABLE, "*", $filter);
            
            if(DB::isEmpty($res)) {
                $this->logout();
                return false;
            }
            
            $info = $res->fetch_assoc();
            
            $info['password'] = $hash;
            
            return $this->_grant($info);
            
        }// logon
        
        
        /**
         * Logout current user
         */
        public function logout() {
            
            $isset = isset($_SESSION['logon']) && $_SESSION['logon'];
            
            // Unset all of the session variables.
            $_SESSION = array();

            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Finally, destroy the session.
            session_destroy();                
            
            if($isset)
            {
                // Notify
                Logs::write(Logs::ACCESS, LogLevel::INFO, 'User logged out.', array(), $this->id);
            }
            
        }// logout        


        /**
         * Verify current user login credentials
         *
         * @return boolean|string|User Returns User object if success, illegal state or FALSE if illegal credentials
         * @throws DBException
         */
        public static function verify() {
            
            $state = false;

            $user = User::current();
            
            if($user !== false && isset($_SESSION['password']))
            {
                $state = $user->_verify($_SESSION['user_id'], $_SESSION['password']);
            }
            elseif(isset($_POST['username']) && isset($_POST['password'])) {
                $user = new User();
                $state = $user->logon(trim($_POST['username']), trim($_POST['password']));
            }
                        
            return $state === true ? $user : $state;
        }// verify


        /**
         * Verify credentials
         *
         * @param string $user_id
         * @param string $hash
         * @return boolean|string
         * @throws DBException
         */
        private function _verify($user_id, $hash) {
            
            $user_id = (int)$user_id;
            
            $filter = "`user_id` = '$user_id' AND `password` = '$hash'";
            
            $res = DB::select(self::TABLE,'*', $filter);
            
            if(DB::isEmpty($res)) {
                $this->logout();
                return false;
            }
            
            $info = $res->fetch_assoc();
            $info['password'] = $hash;
            
            return $this->_grant($info);
            
        }// _verify

        
        private function _grant($info) {
            
            $this->id = (int)$info['user_id'];
            
            $exclude = array("user_id", 'password');

            foreach($info as $property => $value){
                
                if(!in_array($property, $exclude)) { 
                    $this->$property = $value;
                }
            }
            
            $res = DB::select('roles', 'role_id', "`user_id` = ".(int)$this->id);
            if(DB::isEmpty($res) === FALSE) {
                $row = $res->fetch_array();
                $this->role_id = (int)$row[0];
            } else {
                User::error("User {$this->id} have no role.");
            }                
            
            $granted = $this->isState(User::ACTIVE);
            
            $isset = isset($_SESSION['logon']) && $_SESSION['logon'];
            
            if($granted) {
                $_SESSION['logon'] = true;
                $_SESSION['user_id'] = $info['user_id'];
                $_SESSION['password'] = $info['password'];
            } else {
                $this->logout();
            }
            
            if($isset === false) {
                User::log($granted ? 'User logged in.' : 'Logon not granted. User is ' . $this->state);
            }
            
            return $granted ? true : $this->state;
            
        }// _login_ok


        /**
         * Check if a user is authorized to access given object
         *
         * @param string $access read/write
         * @param string $resource resource to access
         * @param mixed $condition Optional condition
         * @return boolean
         * @throws DBException
         */
        public function allow($access, $resource, $condition = null) {
            
            if($this->role_id === null) {
                return false;
            }
                        
            $perms = Roles::getPermissionsForRole($this->role_id);
            
            if(($allow = isset($perms[$resource.':'.$access]))) {
                
                // Check conditions
                switch($resource) {
                    case 'user':
                    case 'setup':               
                        return ($condition === null ? $this->id : $condition) == $this->id;
                    case 'traces':
                        if($condition !== null) {
                            $sql = "SELECT COUNT(*) FROM `traces`
                                WHERE `trace_id`=".(int)$condition." AND `user_id`=".(int)$this->id;
                        }
                        break;
                    default:
                        break;
                }

                // Check if user already own given resources
                if(isset($sql))
                {
                    $res = DB::query($sql);

                    if(DB::isEmpty($res) === FALSE) {

                        $row = $res->fetch_row();

                        $allow = ($row[0] > 0);

                    }
                }

            }
            
            return $allow;
            
        }
        
        
        /**
         * Make hash
         * @param string $string
         * @param string $salt
         * @return string
         */
        public static function hash($string, $salt = '') {
            return sha1((empty($salt) ? SALT : $salt) . $string . '^[]|2"!#');
        }// hash

        
        /**
         * Make safe string
         * 
         * @param string $string
         * @return string
         */
        public static function safe($string) {
            return preg_replace('/[^a-z0-9.@_\-\+]/', '', $string);
        }// safe


        /**
         * Check if user is unique
         *
         * @param $email
         * @return string
         * @throws DBException
         * @internal param string $string
         */
        public static function unique($email) {
            $email = User::safe($email);
            return $email && DB::count(self::TABLE, 'NOT `state`="deleted" AND `email` = "'.  strtolower($email) . '"') === 0;
        }// safe
        

        /** 
         * Get random string of given length
         * 
         * @param integer $length String lengt
         * @return string
         */
        public static function generate($length = PASSWORD_LENGTH)
        {
            return str_rnd($length);
        }// generate
    
        
        private static function log($message, $level = LogLevel::INFO, $result = true)
        {
            $context['code'] = DB::errno();
            $context['error'] = DB::error();
            Logs::write(
                Logs::ACCESS, 
                $level, 
                $message
            );
                
            return $result;
        }                
        
        private static function error($message, $context = array())
        {
            $context['code'] = DB::errno();
            $context['error'] = DB::error();
            Logs::write(
                Logs::SYSTEM, 
                LogLevel::ERROR, 
                $message, 
                $context
            );
                
            return false;
        }
        

    }// user
