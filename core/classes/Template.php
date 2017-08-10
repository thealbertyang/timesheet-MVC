<?php

class Template {

	static $css; 
	static $js;
	static $imgPath;
	static $data;

	public static function init(){		
		self::$css[] = "https://necolas.github.io/normalize.css/5.0.0/normalize.css";
		self::$css[] = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
		self::$css[] = "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css";
		self::$css[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css";
		
		self::$css[] = "//cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css";
		self::$css[] = APP_URL."/templates/default/style.css";
		self::$css[] = APP_URL."/public/css/selectize.default.css";
		

		self::$js[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js";
		self::$js[] = "//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js";
		self::$js[] = APP_URL."/templates/default/custom.js";
		self::$js[] = APP_URL."/public/js/pdfkit/pdfkit.js";
		self::$js[] = APP_URL."/public/js/blob/blob-stream.js";
		self::$js[] = APP_URL."/public/js/moment.js";
		self::$js[] = APP_URL."/public/js/selectize.js";


		//PDFKIT
		$path = APP_PATH."/public/images/pdftemplate.jpg";
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$data = file_get_contents($path);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		self::$data['pdfTemplate'] = $base64;

		$path = APP_PATH."/public/images/pdftemplateca.jpg";
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$data = file_get_contents($path);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		self::$data['pdfTemplateCa'] = $base64;

		self::$imgPath = APP_URL."/public/images";
		self::$data['appDir'] = APP_DIR;



	}
}

?>