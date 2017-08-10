<?php

class View {
	
	public function __construct(){
		Template::init();
		$this->css = Template::$css;
		$this->js = Template::$js;
		$this->imgPath = Template::$imgPath;

		foreach(Template::$data as $dataK => $dataV){
			$this->$dataK = $dataV;
		}

		$this->get = $_GET;
		$this->post = $_POST;
		$this->form = [];
	}

	public function render($filePath) {
		require(APP_PATH."/views". $filePath .".php");
	}
}

?>