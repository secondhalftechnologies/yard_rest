<?php 


	/**
	 * Get kyc_knowledge by farmer id
	 * It is just an example you will have to get the current agent_id then list his farmer list
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->get('/kyc_knowledge/:id', 'authenticate', function($id) use ($app) {

		$response = [];
		global $user_id;
		$db = new DbHandler();

		if (isset($id)) {

			//will have to fetch data from database
			//eg. select * from kyc_knowledge where farmer_id = [id]
			$kyc_knowledge	= $db->getKycKnowladge($user_id, $id);
			// $kyc_knowledge = [
			// 	"f2_edudetail" => 'illiterate',
			// 	"f2_participation" => 'no',
			// 	"f2_points" => '2.33',
			// 	"f2_proficiency" => 'read only',
			// ];

			$response["success"] = true;
			$response["data"] = $kyc_knowledge;

		}
		else{
			$response["success"] = false;
			$response["message"] = " parameter Id is missing!";
		}

		echoResponse(200, $response);
	});


	/**
	 *  Add new Farmer kyc_knowledge to the data base with Post method
	 *  The following post method will create a kyc_knowledge row in data base
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->post('/kyc_knowledge', 'authenticate', function() use ($app){
		verifyRequiredParams([ 
								"fm_id",
								"fm_caid",
								"f2_points",
								"f2_edudetail",
								"f2_proficiency",
								"f2_participation"]); //provide a list of required parametes

		// "f2_typeprog",
		// "f2_durprog",
		// "f2_condprog",
		// "f2_cropprog"
		global $user_id;
		$response = [];
		$kyc_data = $app->request->post(); //fetching the post data into variable
		
		// ------------------------
		// Do Validation here
		// ------------------------

		//Do database instertion here
		$successInsertion = true;

		$db = new DbHandler();

		$farmer_kyc_data	= $db->crateKycKnowladgeEntry($kyc_data, $user_id);

		if ($farmer_kyc_data != false) {
			$response["success"] = true;
			$response["message"] = "applicant knowledge added successfully!";
		} else {
			$response["success"] = false;
			$response["message"] = "Failed to Add applicant knowledge. Please try again";
		}
		echoResponse(201, $response);
	});


	/**
	 *  Update kyc_knowledge to the data base with Put method
	 *  The following put method will update a kyc_knowledge row in data base
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->put('/kyc_knowledge/:id', 'authenticate', function() use ($app){
		verifyRequiredParams([ 
								"fm_id",
								"fm_caid",
								"f2_points",
								"f2_edudetail",
								"f2_proficiency",
								"f2_participation"]); //provide a list of required parametes
		$response = [];
		global $user_id;
		$kyc_data = $app->request->put(); //fetching the post data into variable
		
		// ------------------------
		// Do Validation here
		// ------------------------

		//Do database instertion here
		$successInsertion = true;

		$db = new DbHandler();

		$farmer_kyc_data	= $db->updateKycKnowladgeEntry($kyc_data, $user_id, $id);

		
		if ($farmer_kyc_data != false) {
			$response["success"] = true;
			$response["message"] = "applicant knowledge updated successfully!";
		} else {
			$response["success"] = false;
			$response["message"] = "Failed to Add applicant knowledge. Please try again";
		}
		echoResponse(200, $response);
	});





 ?>