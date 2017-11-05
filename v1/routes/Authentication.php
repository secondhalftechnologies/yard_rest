<?php 

/**
* User Login
* url - /login
* method - POST
* params - email, password
*/
$app->post('/login', function() use ($app) {
	verifyRequiredParams(array('email', 'password'));
	// reading post params
	$email = $app->request()->post('email');
	$password = $app->request()->post('password');

	$response = array();
	$db = new DbHandler();
	// check for correct email and password
	if ($db->checkLogin($email, $password)) {
		// get the user by email
		$user = $db->getUserByEmail($email);
		if ($user != NULL) {
			$response["success"] = true;
			$response["data"] = $user;
		} else {
			// unknown error occurred
			$response['success'] = false;
			$response['data'] = [ "message" => "An error occurred. Please try again" ];
		}
	} else {
		// user credentials are wrong
		$response['success'] = false;
		$response['data'] = [ "message" => "Login failed. Incorrect credentials" ];
	}
	echoResponse(200, $response);
});



?>