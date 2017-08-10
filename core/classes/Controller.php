<?php

class Controller {

	public $defaultControllers = [
									'administrator',
									'login'
								 ];

	public $defaultController = 'administrator';

	public function __construct(){
		$this->view = new View();
	}

	public function loadModel($controller){
		//Load Controller
		$modelClassName = ucfirst($controller)."Model";
		$modelFile = APP_PATH."/models/".$modelClassName.".php";
		require_once($modelFile);
		$this->model = new $modelClassName();
		$this->model->request = $this->request;
	}

	public function loadVendor($directoryAndFileName,$className){
		//Load Vendor
		$vendorFile = APP_PATH."/vendors/".$directoryAndFileName.".php";
		if(file_exists($vendorFile)){
			require_once($vendorFile);
			$this->vendor[$className] = new $className;
			$this->model->vendor[$className] = $this->vendor[$className];
		}
	}

}

?>