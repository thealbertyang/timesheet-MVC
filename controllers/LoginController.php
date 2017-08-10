<?php

class LoginController extends Controller {

	public function __construct(){
		parent::__construct();
	}

	public function index(){
		
		//Define Page Meta
		$this->view->page['name'] = $this->request->controller;

		//If we receive a post from self, then try to authenthicate
		if(!empty($this->request->post)){
			$this->view->form = $this->model->auth($_POST['username'], $_POST['password']);	
		}

		$this->view->render("/header");
		$this->view->render("/login/index");
		$this->view->render("/footer");
	}

	public function auth($params = NULL){

		//Define Page Meta
		$this->view->page['name'] = $this->request->controller;

		//Try to authenticate and place return info into form for page
		$this->view->form = $this->model->auth($_POST['username'], $_POST['password']);

		$this->view->render("/header");
		$this->view->render("/login/index");
		$this->view->render("/footer");
	}

}

?>