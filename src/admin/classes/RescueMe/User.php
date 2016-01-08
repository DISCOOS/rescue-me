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
    use RescueMe\SMS\Provider;
    use Symfony\Component\Security\Core\User\UserInterface;

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
    class User implements UserInterface {
        
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
         * User passord (encrypted)
         * @var string
         */
        public $password;


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
         * Role name
         * @var integer
         */
        public $role_name = null;

        /**
         * User state
         * @var integer
         */
        public $state = null;
        
        
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
         * Get user state titles
         * @return array
         */
        public static function getStates() {
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
         */
        public static function count($states=null, $filter = '') {

            if(isset($states) && is_string($states)) {
                $states = array($states);
            }

            if(isset($states) === FALSE || in_array(User::ALL, $states)) {
                $states = User::$all;
            }
            
            $where = array();
            foreach((array)$states as $state) {
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
         * Get users as row arrays
         * @param string|array $states User state (optional, default: null, values: {'pending', 'disabled', 'deleted'})
         * @param string $filter
         * @param int $start
         * @param bool $max
         * @return boolean|array
         */
        public static function getRows($states = null, $filter = '', $start = 0, $max = false) {

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
                $filter = empty($filter) ? $where : '(' . $filter . ') AND ' . $where;
            }

            if($filter) {
                $filter .= " AND ";
            }
            $filter .= "(`users`.`user_id` = `roles`.`user_id`)";

            $limit = ($max === false ? '' : "$start, $max");

            $res = DB::select(array(self::TABLE, Roles::TABLE), "*", $filter, "`state`, `name`", $limit);
            
            if (DB::isEmpty($res)) return false;

            $rows = array();
            $roles = Roles::getOptions();
            while ($row = $res->fetch_assoc()) {
                $row['role'] = $roles[$row['role_id']];
                $rows[$row['user_id']] = $row;
            }
            return $rows;
            
        }// getRows


        /**
         * Get all users in database
         * @param string|array $states User state (optional, default: null, values: {'pending', 'disabled', 'deleted'})
         * @param string $filter
         * @param int $start
         * @param bool $max
         * @return boolean|array
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
                $user = self::newInstance($row);
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
         */
        public static function current() {
            return isset($_SESSION['user_id']) ? User::get($_SESSION['user_id']) : false;
        }


        /**
         * Get user id from SMS provider reference id
         *
         * @param string $provider
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
            $operation = Operation::get($row[0]);
            return $operation ? $operation->user_id : false;
        }


        /**
         * Get user with given query
         *
         * @param string $select User select query
         * @param \RescueMe\User Update user instance
         *
         * @return boolean|\RescueMe\User
         */
        public static function select($select, $user = null) {

            $res = DB::select(self::TABLE,'*', $select);

            if (DB::isEmpty($res)) return false;

            $row = $res->fetch_assoc();

            return self::newInstance($row, $user);

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
            return self::select("`user_id` = ".(int)$id, $user);
        }

        /**
         * Create User instance from data
         *
         * @param array $data User data
         * @param \RescueMe\User Update user instance
         *
         * @return boolean|\RescueMe\User
         */
        public static function newInstance($data, $user = null) {

            if($user === null) {
                $user = new User();
            }
            foreach($data as $property => $value){
                $user->$property = $value;
            }

            $user->id = (int)$data['user_id'];

            // Resolve role id?
            if (!isset($user->role_id)) {
                $res = DB::select('roles', 'role_id', "`user_id` = ".(int)$user->id);
                if(DB::isEmpty($res) === FALSE) {
                    $row = $res->fetch_array();
                    $user->role_id = (int)$row[0];
                }

            }

            return $user;

        }

        
        
        /**
         * Recover user
         * 
         * @param integer|string $key
         * @param array $methods 
         * 
         * @return boolean|string
         */
        public static function recover($key, $methods = array()) {
            
            $filter = "`".(is_int($key) ? 'id' : 'email')."` = '$key'";
            
            $res = DB::select(self::TABLE,"user_id", $filter);

            if(DB::isEmpty($res)) 
            {
                return User::error(T_('User not found').' '.T_('Reset password not sent'), func_get_args());
            }
            
            $row = $res->fetch_row();
            
            $user = self::get($row[0]);
            
            $password = $user->reset();
            
            $message = $password."\n".sprintf(T_('Your single-use %1$s password'), TITLE);
            
            $res = $user->send($message, $methods);
            
            if($res !== false) {
                return User::log(sprintf(T_('Reset password sent to user %1$s'), $row[0]));
            } 
            
            return User::error(sprintf(T_('Failed to send reset password to user %1$s'),$row[0]), func_get_args());
            
            
        }// recover
        
        
        /**
         * Create new user
         * 
         * @param string $name
         * @param string $email
         * @param string $password Hashed password
         * @param string $country
         * @param string $mobile
         * @param integer $role
         * @param string $state
         * @return User|boolean
         */
        public static function create($name, $email, $password, $country, $mobile, $role, $state = User::ACTIVE) {

            $user = false;

            $username = User::safe(strtolower($email));

            if(empty($username) || empty($password) || User::unique($email) === false) {
                return false;
            }
            
            $values = array((string) $name, (string) $password, (string) $username, (int) $mobile, (string) $country, $state);
            
            $values = \prepare_values(User::$insert, $values);
            
            $res = false;
            
            if(($id = DB::insert(self::TABLE, $values)) !== false) {
                $user = self::get($id);
                $res = Roles::grant($role, $user->id);
            }
            
            if($res !== false) {
                return User::log(sprintf(T_('User %1$s is created.'), $user->id), LogLevel::INFO, $user);
            } 
            
            return User::error(T_('Failed to create user'), func_get_args());
            
            
        }// create


        /**
         * Get User as associative array
         * @return array
         */
        public function toArray() {
            return (array)$this;
        }


        /**
         * Returns the roles granted to the user.
         *
         * <code>
         * public function getRoles()
         * {
         *     return array('ROLE_USER');
         * }
         * </code>
         *
         * Alternatively, the roles might be stored on a ``roles`` property,
         * and populated in any number of different ways when the user object
         * is created.
         *
         * @return Role[] The user roles
         */
        public function getRoles() {
            return array('ROLE_'.strtoupper(Roles::getName($this->role_id)));
        }

        /**
         * Returns the salt that was originally used to encode the password.
         *
         * This can return null if the password was not encoded using a salt.
         *
         * @return string|null The salt
         */
        public function getSalt() {
            return SALT;
        }

        /**
         * Returns the password used to authenticate the user.
         *
         * This should be the encoded password. On authentication, a plain-text
         * password will be salted, encoded, and then compared to this value.
         *
         * @return string The password
         */
        public function getPassword() {
            return $this->password;
        }

        /**
         * Returns the username used to authenticate the user.
         *
         * @return string The username
         */
        public function getUsername() {
            return $this->email;
        }

        /**
         * Removes sensitive data from the user.
         *
         * This is important if, at any given point, sensitive information like
         * the plain-text password is stored on this object.
         */
        public function eraseCredentials()
        {
            // TODO: Implement eraseCredentials() method.
        }

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
         * @param boolean|integer $length Length of random password
         * 
         * @return string|boolean
         */
        public function reset($length = false) {

            if(!$length) {
                $length = Context::getSecurityPasswordLength();
            }
            
            $password = self::generate($length);

            return $this->password($password) ? $password : false;
            
        }// reset
        
        
        /**
         * Set user password.
         * 
         * @param string $password Hashed password
         * 
         * @return boolean
         */
        public function password($password) {
            
            $values = \prepare_values(array("password"), array($password));
            
            if(false !== DB::update(self::TABLE, $values, "user_id=$this->id")) {
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
            
            $values = \prepare_values(array("state"), array(User::DELETED));
            
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
            
            $values = \prepare_values(array("state"), array(User::DISABLED));
            
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
                        return ($condition === null ? $this->id : $condition->id) == $this->id;
                    case 'operations':
                        if($condition !== null) {
                            $sql = "SELECT COUNT(*) FROM `operations` 
                                WHERE `op_id`=".(int)$condition->op_id." AND `user_id`=".(int)$this->id;
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
            return preg_replace('/[^a-z0-9.@_\-\+]/', '', $string);
        }// safe
        

        /**
         * Check if user is unique
         * 
         * @param string $string
         * @return string
         */
        public static function unique($email) {
            $email = User::safe($email);
            return $email && DB::count(self::TABLE, '`email` = "'.  strtolower($email) . '"') === 0;
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
