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
         * Check if one or more users exist
         * 
         * @return boolean
         */
        public static function isEmpty() {
            
            $result = DB::query("SELECT count(*) FROM `users`");
            
            if (DB::isEmpty($result)) return false;
            
            $row = $result->fetch_row();

            return !$row[0];
            
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
                trigger_error("Missing not found", E_USER_WARNING);
                return false;
            }
            $row = $result->fetch_row();
            $operation = Operation::getOperation($row[0]);
            return $operation ? $operation->user_id : false;
        }

        
        /**
         * Get user with given id
         * 
         * @param integer $id User id
         * 
         * @return boolean|\RescueMe\User
         */
        public static function get($id) {
            
            $res = DB::query("SELECT * FROM `users` WHERE `user_id` = $id");

            if (DB::isEmpty($res)) return false;
            
            $exclude = array("user_id", 'password');

            $user = new User();
            $row = $res->fetch_assoc();
            foreach($row as $property => $value){
                
                if(!in_array($property, $exclude)) { 
                    $user->$property = $value;
                }
            }
            
            $user->id = (int)$id;
            
            return $user;
            
        }// get
        
        
        /**
         * Recover user
         * 
         * @param string $email
         * @param string $country
         * @param string $mobile
         * 
         * @return boolean
         */
        public static function recover($email, $country, $mobile) {
            
            $res = DB::query("SELECT user_id FROM `users` WHERE `email` = '$email' AND `mobile_country` = '$country' AND `mobile` = '$mobile'");

            if (DB::isEmpty($res)) return false;
            
            $row = $res->fetch_row();
            
            $user = self::get($row[0]);
            
            $password = $user->reset();
            
            $message = "$password\nYour " . APP_URL . " password.";
            
            return $user->send($message);
            
        }// recover
        
        
        /**
         * Create new user
         * 
         * @param string $name
         * @param string $email
         * @param string $password
         * @param string $country
         * @param string $mobile
         * @return boolean
         */
        public static function create($name, $email, $password, $country, $mobile) {
            
            $username = User::safe(strtolower($email));

            $password = User::hash($password);

            if(empty($username) || empty($password))
                return false;
            
            $values = array((string) $name, (string) $password, (string) $email, (int) $mobile, (string) $country);
            
            $values = \prepare_values(User::$insert, $values);
            
            if(($id = DB::insert(self::TABLE, $values)) !== false) {
                $user = self::get($id);
                $user->prepare();
                return $user;
            }
            
            return false;
            
        }// create
        

        /**
         * Update user
         * 
         * @param string $name
         * @param string $email
         * @param string $country
         * @param string $mobile
         * @return boolean
         */
        public function update($name, $email, $country, $mobile) {
            
            $username = User::safe(strtolower($email));

            if(empty($username))
                return false;
            
            $values = array((string) $name, (string) $email,  (int) $mobile, (string) $country);
            
            $values = \prepare_values(User::$update, $values);
            
            return DB::update(self::TABLE, $values, "user_id=$this->id");
            
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
            
            return $result !== false;
            
        }// password
        
        
        /**
         * Delete user.
         * 
         * @return boolean
         */
        public function delete() {
            
            $values = \prepare_values(array("state"), array("deleted"));
            
            $result = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            return $result !== false;
            
        }// delete        

        
        /**
         * Disable user.
         * 
         * @return boolean
         */
        public function disable() {
            
            $values = \prepare_values(array("state"), array("disabled"));
            
            $result = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            return $result !== false;
            
        }// disable        

        
        /**
         * Enable user.
         * 
         * @return boolean
         */
        public function enable() {
            
            $values = \prepare_values(array("state"), array("NULL"));
            
            $result = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            return $result !== false;
            
        }// disable     
        
        
        /**
         * Send message to user
         * 
         * @param string $message
         * 
         * @return boolean
         */
        public function send($message) {
            
            $sms = Module::get("RescueMe\SMS\Provider", User::currentId())->newInstance();
            if(!$sms)
            {
                insert_error("Failed to get SMS provider");
                return false;
            }

            $res = $sms->send(SMS_FROM, $this->mobile_country, $this->mobile, $message);
            if(!$res) {
                insert_error($sms->error());
            }
            return $res;
            
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
            
            $sql = "SELECT * FROM `users` WHERE `email` = '$username' AND `password` = '$password'";
            
            $res = DB::query($sql);
            
            if(mysqli_num_rows($res) == 1) {
                return $this->_grant($res->fetch_assoc());
            }            
            return false;
            
        }// logon

        
        /**
         * Verify credentials
         * 
         * @param string $user_id
         * @param string $password
         * @return boolean
         */
        private function _verify($user_id, $password) {
            $username = (int) $user_id;
            $password = $password;
            $qry = "SELECT * FROM `users` WHERE `user_id` = '$username' AND `password` = '$password'";
            $sql = DB::query($qry);
            if(mysqli_num_rows($sql) == 1) {
                return $this->_grant(mysqli_fetch_assoc($sql));
            }
            return false;
        }// _verify

        
        private function _grant($info) {
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
            
            return true;
        }// _login_ok

        
        /**
         * Verify current user credentials
         * 
         * @return boolean
         */
        public function verify() {

            if(isset($_SESSION['user_id']) && isset($_SESSION['password']))
            {
                if($this->_verify($_SESSION['user_id'], $_SESSION['password'])) {
                    return true;
                }
                $this->logout();
            }
            elseif(isset($_POST['username']) && isset($_POST['password'])) {
                return $this->logon($_POST['username'], $_POST['password']);                
            }
            return false;
        }// verify
        
        /**
         * Check if a user is allowed to perform something
         * @param string $access read/write
         * @param string $object 
         * @param int $obj_id
         * @return boolean
         */
        public function allow($access, $object, $obj_id) {
            switch($object) {
                case 'operation':
                    $sql = "SELECT `op_id` FROM `operations` 
                        WHERE `op_id`=".(int)$obj_id." AND `user_id`=".(int)$this->id;
                    break;
                case 'missing':
                    $sql = "SELECT `operations`.`op_id` FROM `operations` 
                        JOIN `missing` ON `missing`.`op_id` = `operations`.`op_id` 
                        AND `missing`.`missing_id`=".(int)$obj_id." AND `user_id`=".(int)$this->id;
                    break;
                default:
                    return false;
                    break;
            }
            
            $res = DB::query($sql);
            
            if(mysqli_num_rows($res) === 1) {
                return true;
            }            
            
            return false;
        }
        
        /**
         * Logout current user
         */
        public function logout() {
            unset($_SESSION['logon']);
            unset($_SESSION['user_id']);
            unset($_SESSION['password']);
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
         * Make hashable string
         * 
         * @param string $string
         * @return string
         */
        public static function safe($string) {
            return preg_replace('/[^a-z0-9.@_-]/', '', $string);
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
    
        

    }// user
