<?php

class user {
	
	function __construct() {
	}
	
	function logon($username, $password) {
		$username = $this->safe(strtolower($username));

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
		$_SESSION['SAVNET_user_id'] = $this->info['user_id'];
		$_SESSION['SAVNET_user_pass']=$this->info['password'];
	}
	
	function verify() {
		if(isset($_SESSION['SAVNET_user_id']) && isset($_SESSION['SAVNET_user_pass']))
			return $this->_verify($_SESSION['SAVNET_user_id'], $_SESSION['SAVNET_user_pass']);
		elseif(isset($_POST['savnet_user']) && isset($_POST['savnet_pass']))
			return $this->logon($_POST['savnet_user'], $_POST['savnet_pass']);
		return false;
	}
	
	
	function hash($str) {
		return sha1('SAVNETntrkH' . $str . '^[]|2"!#');
	}
	
	function safe($str) {
		return preg_replace('/[^a-z0-9.@_-]/', '', $str);
	}
	
}