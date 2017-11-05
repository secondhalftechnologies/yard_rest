<?php

// Note by Ejaz ==============================================
// The link for the reference of this restfull api is "http://blog.aimanbaharum.com/2015/07/05/slim-framework-crud/"
// Just go throw All the code in the reference link and you will understand the core functionality easily
// End of Note by Ejaz =======================================
// sleep(5);

require_once '../include/DbHandler.php';
require_once '../include/Utils.php';
require '../vendor/autoload.php';

$app = new \Slim\Slim();
if(SLIM_DEBUG){
  $app->config('debug',true);
}


// $corsOptions = array(
//     "origin" => "*",
//     "maxAge" => 1728000,
//     "allowCredentials" => True,
//     "allowMethods" => array("GET", "POST", "PUT", "DELETE", "OPTIONS"),
//     "allowHeaders" => array("Origin", "Content-Type", "Accept", "authorization")
//     );
// $app->add(new \CorsSlim\CorsSlim($corsOptions));


/** ==============================================
 * Nothing was working, everything was going wrong,
 * I found above block of code that says to solve the cors issue but not worked, tried many more suggestions.
 * I spend more than 12 hours to find the proper solution of cors.
 * After a stuck movement, now(3:04 am) I found this awesome block of code that shows the ability of a programmer.
 * Hats off!!!
 * Remember to remove the following code in production mode.
 ==============================================*/
	
	// Allow from any origin
	if (isset($_SERVER['HTTP_ORIGIN'])) {
	    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	    header('Access-Control-Allow-Credentials: true');
	    // header('Access-Control-Max-Age: 86400');    // cache for 1 day
	}

	// Access-Control headers are received during OPTIONS requests
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

	    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
	        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

	    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
	        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	    exit(0);
	}

//=============================================




/**
* route test block
*/
$app->get('/', function () {
    echo "restfull api v1";
});
$app->get('/test/:name', function ($name) {
    echo "Hello, $name";
});


require './routes/Authentication.php';
require './routes/Farmers.php';
// LMS File
require './routes/lms.php';

// KYC Part
require './routes/Kyc_knowledge.php';
require './routes/Kyc_spouse_details.php';
// require './routes/Kyc_phone_details.php';
// require './routes/Kyc_family_details.php';
// require './routes/Kyc_appliances_motors.php';

// // Farm Land Part
// require './routes/Farm_land_details.php';

// // CROP Part
// require './routes/Crop_cultivation_details.php';
// require './routes/Crop_prev_crop_details.php';
// require './routes/Crop_cur_crop_details.php';

// // Assets Part
// require './routes/Assets_details.php';
// require './routes/Live_stock_details.php';

// // Loan Part
// require './routes/Loan_financial_details.php';
// require './routes/Loan_financial_history.php';


// Run Slim
$app->run();