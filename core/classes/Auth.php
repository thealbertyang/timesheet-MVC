<?php

class Auth {
	public static function getRole(){
		return $_SESSION['role'];
	}

	public static function handleLogin(){
		@session_start();
        if (!isset($_SESSION['loggedIn'])) {
            $url = $_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/login/";
			header('Location: http://' . $url);
	    	exit();
        }
	}

	public static function handleLoggedIn() {
		if(isset($_SESSION)) { 
		    if ($_SESSION['loggedIn'] == true) {
			    $url = $_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/administrator/";
				header('Location: http://' . $url);
		    	exit();
		    }
		}
	}

	public static function logOut(){
		if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == true) {
			@session_destroy();
			$_SESSION = array();
		    $url = $_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/login/"; 	
			header('Location: http://' . $url);
	    	exit();
	    }

	}
}
