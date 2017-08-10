<?php 

//Include config file first. First check main location. Then check one level up.
if(file_exists(dirname(__FILE__)."/config.php")){
	//We got it. Let's continue.
	require_once dirname(__FILE__)."/config.php";
}
else if(file_exists(dirname(dirname(__FILE__)."/config.php"))) {
	//It's up one level. Let's continue.
	require_once dirname(dirname(__FILE__))."/config.php";	
}
else {
	//There is no config file. We should let the user know. 
	die("Sorry the website is currently down. Please check back in a couple hours.");
}

//LOAD CORE FILES AUTOMATICALLY
function __autoload($class) {
    require CORE_PATH."/classes/" . $class .".php";
}

//BOOTSTRAP
$app = new Bootstrap();
$app->init();

?>