<?php

class Router {

	public function __construct(){
		//Variables
		$this->url = $this->extractURL();
		$this->controller = $this->extractController($this->url);
		$this->action = $this->extractAction($this->url);
		$this->params = $this->extractParams($this->url);
		$this->post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$this->get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		
		if(!isset($_SESSION)){ @session_start(); }
		$this->session = $_SESSION;
	}


	function reRoute($location){

		$url = $_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/".$location;
		if(strpos($_SERVER['HTTP_HOST'],'http') === false){
			$url = "http://".$url;
		}
		
		if(!headers_sent()){
			header('Location:' . $url);
			exit;
		}
		else {
			echo "<script> window.location.href = '".$url."'; </script>";
		}
	}

	function extractURL(){
		$url = explode('/', filter_var(trim($_SERVER['REQUEST_URI'], '/'), FILTER_SANITIZE_URL));

		return $url;
	}
	
	function extractController($url){

		//Find out which level the controller is at
		$controllerIndex = count(explode('/', trim(APP_DIR, '/')));

		if(!isset($url[$controllerIndex])){ return null; } else {
			return $url[$controllerIndex];
		}

	}

	function extractAction($url){

		//Find out which level the action are at
		$actionIndex = count(explode('/', trim(APP_DIR, '/'))) + 1;

		if(!isset($url[$actionIndex])){ return null; } else {
			return $url[$actionIndex];
		}
	}

	function extractParams($url){
		
		$params = array();

		//Find out which level the params are at
		$paramsIndex = count(explode('/', trim(APP_DIR, '/'))) + 2;

	    foreach($url as $key => $value){
	        if($key >= $paramsIndex){
	          array_push($params,$value);
	        } 
	    }

	    if(!isset($params[0])){
	    	//$params[0] = "";
	    }

	    return $params;
	}

}