<?php

class user {
	
	function __construct() {
	}
    
    function create($name, $email, $password) {

        $username = $this->safe(strtolower($email));

        $password = $this->hash($password);

        if(empty($username) || empty($password))
            return false;

        $query = "INSERT INTO `users` (`user_id`,`name`,`email`,`password`) VALUES(NULL, '$name', '$email', '$password');";
        
        if(SQLcon()->query($query)) {
            return $this->logon($email, $password);
        }
        return false;
    }    
	
	function logon($email, $password) {
        
		$username = $this->safe(strtolower($email));

		$password = $this->hash($password);
		if(empty($username) || empty($password))
			return false;
		$qry = "SELECT * FROM `users` WHERE `email` = '$username' AND `password` = '$password'";
#		var_dump($qry);
		$sql = mysqli_query(SQLcon(), $qry);
		if(mysqli_num_rows($sql) == 1) {
			$this->_logon_ok(mysqli_fetch_assoc($sql));
			return true;
		}
		return false;
	}
	
	function _verify($user_id, $password) {
		$username = (int) $user_id;
		$password = $password;
		$qry = "SELECT * FROM `users` WHERE `user_id` = '$username' AND `password` = '$password'";
		$sql = mysqli_query(SQLcon(), $qry);
		if(mysqli_num_rows($sql) == 1) {
			$this->_logon_ok(mysqli_fetch_assoc($sql));
			return true;
		}
		return false;
	}
	
	function _logon_ok($info) {
		$this->info = $info;
        $_SESSION['logon'] = true;
		$_SESSION['user_id'] = $this->info['user_id'];
		$_SESSION['password']=$this->info['password'];
	}
	
	function verify() {
		if(isset($_SESSION['user_id']) && isset($_SESSION['password']))
			return $this->_verify($_SESSION['user_id'], $_SESSION['password']);
		elseif(isset($_POST['username']) && isset($_POST['password']))
			return $this->logon($_POST['username'], $_POST['password']);
		return false;
	}
    
    function logout() {
		unset($this->info);
		unset($_SESSION['logon']);
		unset($_SESSION['user_id']);
        unset($_SESSION['password']);
    }	
	
	function hash($str) {
		return sha1(SALT . $str . '^[]|2"!#');
	}
	
	function safe($str) {
		return preg_replace('/[^a-z0-9.@_-]/', '', $str);
	}
	
}