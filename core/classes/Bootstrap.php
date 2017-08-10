<?php

class Bootstrap {
	
	private $request;
	private $controller;
	private $defaultController = 'administrator';

	public function init(){

		$this->request = new Router;

		if(empty($this->request->controller)){
			$this->request->reRoute($this->defaultController);
			return false;
		}
		
		//Load Controller
		$controllerClassName = ucfirst($this->request->controller)."Controller";
		$controllerFile = APP_PATH."/controllers/".$controllerClassName.".php";

		if(file_exists($controllerFile)){			
			require_once($controllerFile);
			$this->controller = new $controllerClassName;
			$this->controller->request = $this->request;
		}

		else {
			echo "404";
			return false;
		}

		//Do Controller	
		if(isset($this->request->action)){
			if(method_exists($this->controller, $this->request->action)){				
				$this->controller->loadModel($this->request->controller);
				$this->loadVendors();
				$this->controller->{$this->request->action}($this->request->params);
			}

			else {
				echo "404";
			}
		}

		else { 				
			$this->controller->loadModel($this->request->controller);		
			$this->loadVendors();
			$this->controller->index($this->request->params);
		}

	}

	public function loadVendors(){
		//Load Vendors
		$this->controller->loadVendor("PHPMailer/class.phpmailer","PHPMailer");	
	}
}