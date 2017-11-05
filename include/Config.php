<?php 


/** Database config */
if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1'){
	define('DB_USERNAME', 'root');  
	define('DB_PASSWORD', '');  
}else
{
	define('DB_USERNAME', 'sqyard_2017');  
	define('DB_PASSWORD', 'Sqyard@!2017');  
}

define('DB_HOST', 'localhost');  
define('DB_NAME', 'sqyard_2017');


/** Debug modes */
define('PHP_DEBUG_MODE', true);  
define('SLIM_DEBUG', true);


