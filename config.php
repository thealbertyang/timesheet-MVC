<?php 

// ** MySQL Settings ** //
define('DB_NAME', '');

/** MySQL database username */
define('DB_USER', '');

/** MySQL database password */
define('DB_PASS', '');

/** MySQL hostname */
define('DB_HOST', '');

/** Define Paths **/
define('APP_PATH', dirname(__FILE__)); // e.g. "/home/user/public_html/portal_directory" 
define('APP_DIR', str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname(__FILE__))); // e.g. "/portal_directory"
define('APP_URL', dirname($_SERVER['PHP_SELF']) ); // e.g. "/home/user/public_html/portal_directory" 
define('APP_FULL_URL', "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])); // e.g. "http://www.domain.com/portal_directory/"
define('CORE_PATH', APP_PATH."/core");	// e.g. "/home/user/public_html/portal_directory/core"
define('ADMIN_PATH', APP_PATH."/admin"); // e.g. "/home/user/public_html/portal_directory/admin"
define('PUBLIC_PATH', APP_PATH."/public");	// e.g. "/home/user/public_html/portal_directory/public"

ini_set("date.timezone", "America/Los_Angeles");
?>