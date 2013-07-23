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
            "mobile"
        );
        
        private static $update = array
        (
            "name", 
            "email", 
            "mobile"
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
         * @return boolean|array
         */
        public static function getAll() {
            
            $res = DB::query("SELECT * FROM `users`");
            
            if (DB::isEmpty($res)) return false;

            $users = array();
            while ($row = $res->fetch_assoc()) {
                $user = self::get($row['user_id']);
                $users[$row['user_id']] = $user;
            }
            return $users;
            
        }// getAll        
        
        
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
            
            $user->id = $id;
            
            return $user;
            
        }// get
        
        
        /**
         * Create new user
         * 
         * @param string $name
         * @param string $email
         * @param string $password
         * @param string $mobile
         * @return boolean
         */
        public static function create($name, $email, $password, $mobile) {
            
            $user = new User();

            $username = User::safe(strtolower($email));

            $password = User::hash($password);

            if(empty($username) || empty($password))
                return false;
            
            $values = array((string) $name, (string) $password, (string) $email,  (int) $mobile);
            
            $values = prepare_values(User::$insert, $values);
            
            if(($id = DB::insert(self::TABLE, $values)) !== false) {
                return $user->_grant(array(
                    'user_id' => $id, 
                    "password" => $password
                ));
            }
            return false;
            
        }// create    
        

        /**
         * Update user
         * 
         * @param string $name
         * @param string $email
         * @param string $mobile
         * @return boolean
         */
        public function update($name, $email, $mobile) {
            
            $username = User::safe(strtolower($email));

            if(empty($username))
                return false;
            
            $values = array((string) $name, (string) $email,  (int) $mobile);
            
            $values = prepare_values(User::$update, $values);
            
            return DB::update(self::TABLE, $values, "user_id=$this->id");
            
        }// update       
        

        /**
         * Reset user password.
         * 
         * Returns random password
         * 
         * @param integer $length Length of random password
         * 
         * @return string|boolean
         */
        public function reset($length = 15) {
            
            $password = str_rnd($length);

            $values = prepare_values(array("password"), array(User::hash($password)));
            
            $result = DB::update(self::TABLE, $values, "user_id=$this->id");
            
            return ($result === false ? false : $password);
            
        }// reset
        
        
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
            $_SESSION['password']=$info['password'];
            $this->id = $info['user_id'];
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
        

    }// user