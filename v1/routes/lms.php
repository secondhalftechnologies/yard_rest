<?php 
	/**
	* Get lms by farmer id
	* It is just an example you will have to get the current agent_id then list his farmer list
	* 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	*/
	$app->get('/lms', 'authenticate', function() use ($app) {

		$response = [];
		
		$db = new DbHandler();

		$lms	= $db->getLMS();
		
		$response["success"] = true;
		$response["data"] = $lms;

		echoResponse(200, $response);
	});
?>