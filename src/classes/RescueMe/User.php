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
     */
    class User {
        
        public $id;
        public $name;
        public $email;
        public $mobile;
        
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
        
        
        public static function get($id) {
            
            $res = DB::query("SELECT * FROM `users` WHERE `user_id` = $id");

            if (DB::isEmpty($res)) return false;

            $user = new User();
            $row = $res->fetch_assoc();
            foreach($row as $property => $value){
                if($property != 'password') $user->$property = $value;
            }
            
            return $user;
            
        }// get
        
        
        public function create($name, $email, $password) {

            $username = $this->safe(strtolower($email));

            $password = $this->hash($password);

            if(empty($username) || empty($password))
                return false;

            $query = "INSERT INTO `users` (`user_id`,`name`,`email`,`password`) VALUES(NULL, '$name', '$email', '$password');";

            if(($id = DB::query($query)) > 0) {
                $info = array('user_id' => $id, "password" => $password);
                $this->_logon_ok($info);
                return true;
            }
            return false;
        }// create    
        

        public function logon($email, $password) {

            $username = $this->safe(strtolower($email));

            $password = $this->hash($password);
            if(empty($username) || empty($password))
                return false;
            $sql = "SELECT * FROM `users` WHERE `email` = '$username' AND `password` = '$password'";
    #		var_dump($qry);
            $res = DB::query($sql);
            if(mysqli_num_rows($res) == 1) {
                $this->_logon_ok(mysqli_fetch_assoc($res));
                return true;
            }
            return false;
        }// logon

        
        public function _verify($user_id, $password) {
            $username = (int) $user_id;
            $password = $password;
            $qry = "SELECT * FROM `users` WHERE `user_id` = '$username' AND `password` = '$password'";
            $sql = DB::query($qry);
            if(mysqli_num_rows($sql) == 1) {
                $this->_logon_ok(mysqli_fetch_assoc($sql));
                return true;
            }
            return false;
        }// _verify

        
        public function _logon_ok($info) {
            $_SESSION['logon'] = true;
            $_SESSION['user_id'] = $info['user_id'];
            $_SESSION['password']=$info['password'];
        }// _login_ok

        function verify() {
            if(isset($_SESSION['user_id']) && isset($_SESSION['password']))
                return $this->_verify($_SESSION['user_id'], $_SESSION['password']);
            elseif(isset($_POST['username']) && isset($_POST['password']))
                return $this->logon($_POST['username'], $_POST['password']);
            return false;
        }// verify

        
        public function logout() {
            unset($_SESSION['logon']);
            unset($_SESSION['user_id']);
            unset($_SESSION['password']);
        }// logout

        
        public function hash($str) {
            return sha1(SALT . $str . '^[]|2"!#');
        }// hash

        
        public function safe($str) {
            return preg_replace('/[^a-z0-9.@_-]/', '', $str);
        }// safe

    }// user