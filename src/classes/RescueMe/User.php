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
    use \RescueMe\Log\Logs;

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
            "mobile_country"
        );
        
        private static $update = array
        (
            "name", 
            "email", 
            "mobile",
            "mobile_country"
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
         * Prevent initialization of user object outside this class
         */
        protected final function __construct()
        {
            
        }

        
        /**
         * Check if one or more users exist
         * 
         * @return boolean
         */
        public static function isEmpty() {
            
            $res = DB::count(User::TABLE);
            
            return $res !== FALSE && $res == 0;
            
        }// isEmpty
        
        
        /**
         * Get all users in database
         * 
         * @param array $states User state (optional, default: null, values: {'pending', 'disabled', 'deleted'})
         * 
         * @return boolean|array
         */
        public static function getAll($states=null) {
            
            $states = isset($states) ? $states : array("", "NULL","pending","disabled");
            foreach(isset($states) ? $states : array() as $state) {
                $filter[] = $state === "NULL" ? "`state` IS NULL" : "`state`='$state'";
            } 
            $filter = implode($filter," OR ");
            
            $res = DB::select(self::TABLE, "*", $filter, "`state`, `name`");
            
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
            return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        }

        
        /**
         * Get current user.
         * 
         * @return boolean|User User instance if found, FALSE otherwise.
         */
        public static function current() {
            return isset($_SESSION['user_id']) ? User::get($_SESSION['user_id']) : false;
        }
        
        
        /**
         * Get user id from SMS provider reference id
         * 
         * @param integer $reference
         * 
         * @return integer|boolean Operation id if success, FALSE otherwise.
         */
        public static function getProviderUserId($provider, $reference) {
            
            
            // Get all missing with given reference
            $select = "SELECT `op_id` FROM `missing` WHERE `sms_provider` = '".$provider."' AND `sms_provider_ref` = '".$reference."';";

            $result = DB::query($select);
            if(DB::isEmpty($result)) {                 
                return User::error("No user id found. $provider reference $reference not found.");                
            }
            $row = $result->fetch_row();
            $operation = Operation::getOperation($row[0]);
            return $operation ? $operation->user_id : false;
        }

        
        /**
         * Get user with given id
         * 
         * @param integer $id User id
         * @param \RescueMe\User Update user instance
         * 
         * @return boolean|\RescueMe\User
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
                
                if(!in_array($property, $exclude)) { 
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
         */
        public static function recover($email, $methods = null) {
            
            $filter = "`email` = '$email'";
            
            $res = DB::select(self::TABLE,"user_id", $filter);

            if(DB::isEmpty($res)) 
            {
                return User::error(_("User not found. Recovery password not sent."), func_get_args());                
            }
            
            $row = $res->fetch_row();
            
            $user = self::get($row[0]);
            
            $password = $user->reset();
            
            $message = "$password\nYour " . APP_URL . " password.";
            
            $res = $user->send($message, $methods);
            
            if($res !== false) {
                return User::log(_("Sent recovery password to user {$row[0]}"));
            } 
            
            return User::error(_("Failed to send recovery password to user {$row[0]}"), func_get_args());
            
            
        }// recover
        
        
        /**
         * Create new user
         * 
         * @param string $name
         * @param string $email
         * @param string $password
         * @param string $country
         * @param string $mobile
         * @param integer $role
         * @return boolean
         */
        public static function create($name, $email, $password, $country, $mobile, $role) {
            
            $username = User::safe(strtolower($email));

            $password = User::hash($password);

            if(empty($username) || empty($password) || User::unique($email) === false) {
                return false;
            }
            
            $values = array((string) $name, (string) $password, (string) $email, (int) $mobile, (string) $country);
            
            $values = \prepare_values(User::$insert, $values);
            
            $res = false;
            
            if(($id = DB::insert(self::TABLE, $values)) !== false) {
                $user = self::get($id);
                $user->prepare();
                $res = Roles::grant($role, $user->id);
            }
            
            if($res !== false) {
                return User::log(_("User {$user->id} created"));
            } 
            
            return User::error(_("Failed to create user"), func_get_args());
            
            
        }// create
        

        /**
         * Update user
         * 
         * @param string $name
         * @param string $email
         * @param string $country
         * @param string $mobile
         * @param mixed $role id or name
         * @return boolean
         */
        public function update($name, $email, $country, $mobile, $role = null) {
            
            $res = false;
            
            $username = User::safe(strtolower($email));

            if(empty($username) === FALSE)
            {
                $values = array((string)$name, (string)$email, (int)$mobile, (string)$country);

                $values = prepare_values(User::$update, $values);

                if(DB::update(self::TABLE, $values, "user_id=$this->id")) {
                    
                    $res = true; 
                    if($role !== null) {
                        $res =Roles::grant($role, $this->id);
                    }
                    
                }
            }
            
            if($res !== false) {
                return User::log("User {$this->id} updated");
            } 
            
            return User::error("Failed to update user {$this->id}", func_get_args());
            
        }// update
        

        /**
         * Prepare user modules if not already exist
         * 
         * @return boolean TRUE if changes was made, FALSE otherwise.
         */
        public function prepare() {
            $changed = false;
            $modules = Module::getAll();
            if($modules != false) {
                foreach($modules as $module) {
                    if(!Module::exists($module->type, $this->id)) {
                        Module::add($module->type, $module->impl, $module->newConfig()->params(), $this->id);
                        $changed = true;
                    }
                }           
            }
            return $changed;
        }
        

        /**
         * Reset user password.
         * 
         * Returns random password
         * 
         * @param integer $length Length of random password
         * 
         * @return string|boolean
         */
        public function reset($length = 8) {
            
            $password = self::generate($length);

            return $this->password($password) ? $password : false;
            
        }// reset
        
        
        /**
         * Set user password.
         * 
         * @param string $tokens Password
         * 
         * @return boolean
         */
        public function password($tokens) {
            
            $password = User::hash($tokens);
            
            $values = \prepare_values(array("password"), array($password));
            
            $result = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($result !== false && isset_get($_SESSION,'user_id') == $this->id) {
                $_SESSION['password'] = $password;
            }
            
            if($result !== false) {
                return User::log("User {$this->id} password changed");
            } 
            
            return User::error("Failed to change user {$this->id} password", $password);
            
        }// password
        
        
        /**
         * Delete user.
         * 
         * @return boolean
         */
        public function delete() {
            
            $values = \prepare_values(array("state"), array("deleted"));
            
            $res = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($res !== false) {
                return User::log("User $this->id deleted");
            }
            
            return User::error("Failed to delete user $this->id", $this);
            
        }// delete        

        
        /**
         * Disable user.
         * 
         * @return boolean
         */
        public function disable() {
            
            $values = \prepare_values(array("state"), array("disabled"));
            
            $res = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($res !== false) {
                return User::log("User $this->id disabled");
            }
            
            return User::error("Failed to disable user $this->id", $this);
            
        }// disable        

        
        /**
         * Enable user.
         * 
         * @return boolean
         */
        public function enable() {
            
            $values = \prepare_values(array("state"), array("NULL"));
            
            $res = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            if($res !== false) {
                return User::log("User $this->id enabled");
            }
            
            return User::error("Failed to enable user $this->id", $this);
            
        }// disable     
        
        
        /**
         * Send message to user devices
         * 
         * @param string $message
         * @param array $devices Send to given devices {'sms','email'};
         * 
         * @return boolean
         */
        public function send($message, $devices = array()) {
            
            $sent = 0;
            
            $devices = array_map(function($device) { return strtolower($device);}, $devices);
            
            $devices = array_intersect(array('sms','email'), $devices);
            
            $all = empty($devices);
            
            if($all || in_array('sms', $devices)) {
            
                $sms = Module::get("RescueMe\SMS\Provider", User::currentId())->newInstance();
                if(!$sms)
                {
                    return User::error("Failed to get SMS provider");
                }

                $res = $sms->send(SMS_FROM, $this->mobile_country, $this->mobile, $message);
                if($res === FALSE) {
                    User::error($sms->error());
                } else {
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
         */
        public function logon($email, $password) {

            $username = User::safe(strtolower($email));

            $password = User::hash($password);
            
            if(empty($username) || empty($password))
                return false;
            
            $res = DB::select(self::TABLE, "*", "`email` = '$username' AND `password` = '$password'");

            if(DB::isEmpty($res)) return false;
            
            $info = $res->fetch_assoc();
            
            $info['password'] = $password;
            
            return $this->_grant($info);
            
        }// logon

        
        /**
         * Verify current user login credentials
         * 
         * @return boolean|User Returns User object if success, FALSE otherwise
         */
        public static function verify() {

            $user = User::current();
            
            if($user !== false && isset($_SESSION['password']))
            {
                if($user->_verify($_SESSION['user_id'], $_SESSION['password'])) {
                    return $user;
                }
                $user->logout();
            }
            elseif(isset($_POST['username']) && isset($_POST['password'])) {
                $user = new User();
                if($user->logon($_POST['username'], $_POST['password'])) {
                    return $user;
                }
            }
            return false;
        }// verify
        
        
        /**
         * Verify credentials
         * 
         * @param string $user_id
         * @param string $password
         * @return boolean
         */
        private function _verify($user_id, $password) {
            
            $user_id = (int)$user_id;
            
            $res = DB::select(self::TABLE,'*', "`user_id` = '$user_id' AND `password` = '$password'");            
            
            if(DB::isEmpty($res)) return false;            
            
            $info = $res->fetch_assoc();
            $info['password'] = $password;
            
            return $this->_grant($info);
            
        }// _verify

        
        private function _grant($info) {
            
            $isset = isset($_SESSION['logon']) && $_SESSION['logon'];
            
            $_SESSION['logon'] = true;
            $_SESSION['user_id'] = $info['user_id'];
            $_SESSION['password'] = $info['password'];
            
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
            
            if($isset === FALSE) {
                User::log('User logged in.');
            }
            
            return true;
            
        }// _login_ok

        
        /**
         * Check if a user is authorized to access given object
         * 
         * @param string $access read/write
         * @param string $resource resource to access
         * @param mixed $condition Optional condition
         * @return boolean
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
                        return (int)($condition === null ? $this->id : $condition) === $this->id;
                    case 'operations':
                        if($condition !== null) {
                            $sql = "SELECT COUNT(*) FROM `operations` 
                                WHERE `op_id`=".(int)$condition." AND `user_id`=".(int)$this->id;
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
         * Logout current user
         */
        public function logout() {
            
            $isset = isset($_SESSION['logon']) && $_SESSION['logon'];
            
            unset($_SESSION['logon']);
            unset($_SESSION['user_id']);
            unset($_SESSION['password']);
            
            if($isset)
            {
                Logs::write(Logs::ACCESS, LogLevel::INFO, 'User logged out.', array(), $this->id);
            }
            
        }// logout

        
        /**
         * Make hash
         * @param string $string
         * @return string
         */
        public static function hash($string) {
            return sha1(SALT . $string . '^[]|2"!#');
        }// hash

        
        /**
         * Make safe string
         * 
         * @param string $string
         * @return string
         */
        public static function safe($string) {
            return preg_replace('/[^a-z0-9.@_-]/', '', $string);
        }// safe
        

        /**
         * Check if user is unique
         * 
         * @param string $string
         * @return string
         */
        public static function unique($email) {
            $email = User::safe($email);
            return $email && DB::count(self::TABLE, '`email` = '.  strtolower($email)) === 0;
        }// safe
        

        /** 
         * Get random string of given length
         * 
         * @param integer $length String lengt
         * @return string
         */
        public static function generate($length = 8)
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
