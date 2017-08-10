<?php

class User {

	static $role;

	public static function getFirstName(){
		return $_SESSION['firstname'];
	}

	public static function getLastName(){
		return $_SESSION['lastname'];
	}

	public static function getUsername(){
		return $_SESSION['username'];
	}

	public static function checkRole($role){
		if(isset($_SESSION['role']) && $_SESSION['role'] == $role){
			return true;
		}
		else {
			return false;
		}
	}
	public static function getRole(){
		return $_SESSION['role'];
	}
}

?>