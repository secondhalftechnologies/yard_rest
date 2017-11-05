<?php 


	/**
	 * Get kyc_spouse_details by farmer id
	 * It is just an example you will have to get the current agent_id then list his farmer list
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->get('/kyc_spouse_details/:id', 'authenticate', function($id) use ($app) {

		$response = [];
		global $user_id;
		$db = new DbHandler();

		if (isset($id)) {

			//will have to fetch data from database
			//eg. select * from kyc_spouse_details where farmer_id = [id]
			$kyc_spouse_details	= $db->getKycSpouseDetails($user_id, $id);
			
			$response["success"] = true;
			$response["data"] = $kyc_spouse_details;

		}
		else{
			$response["success"] = false;
			$response["message"] = " parameter Id is missing!";
		}

		echoResponse(200, $response);
	});


	/**
	 *  Add new Farmer kyc_spouse_details to the data base with Post method
	 *  The following post method will create a kyc_spouse_details row in data base
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->post('/kyc_spouse_details', 'authenticate', function() use ($app){
		verifyRequiredParams([ 
								"fm_id",
								"fm_caid",
								"f3_points",
								"f3_married_reg_points",
								"f3_spouse_fname",
								"f3_spouse_age",
								"f3_spouse_adhno",
								"f3_spouse_occp",
								"f3_spouse_owned_prop",
								"f3_spouse_prop_type",
								"f3_spouse_mfi",
								"f3_affliation_status",
								"f3_spouse_mobno"]); //provide a list of required parametes

		global $user_id;
		$response = [];
		$kyc_data = $app->request->post(); //fetching the post data into variable
		
		// ------------------------
		// Do Validation here
		// ------------------------

		//Do database instertion here
		$successInsertion = true;

		$db = new DbHandler();

		$farmer_kyc_data	= $db->crateKycSpouseEntry($kyc_data, $user_id);

		if ($farmer_kyc_data != false) {
			$response["success"] = true;
			$response["message"] = "spouse details added successfully!";
		} else {
			$response["success"] = false;
			$response["message"] = "Failed to Add spouse details. Please try again";
		}
		echoResponse(201, $response);
	});


	/**
	 *  Update kyc_spouse_details to the data base with Put method
	 *  The following put method will update a kyc_spouse_details row in data base
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->put('/kyc_spouse_details/:id', 'authenticate', function() use ($app){
		verifyRequiredParams([ 
								"fm_id",
								"fm_caid",
								"f3_points",
								"f3_married_reg_points",
								"f3_spouse_fname",
								"f3_spouse_age",
								"f3_spouse_adhno",
								"f3_spouse_occp",
								"f3_spouse_owned_prop",
								"f3_spouse_prop_type",
								"f3_spouse_mfi",
								"f3_affliation_status",
								"f3_spouse_mobno"]); //provide a list of required parametes
		$response = [];
		global $user_id;
		$kyc_data = $app->request->put(); //fetching the post data into variable
		
		// ------------------------
		// Do Validation here
		// ------------------------

		//Do database instertion here
		$successInsertion = true;

		$db = new DbHandler();

		$farmer_kyc_data	= $db->updateKycSpouseEntry($kyc_data, $user_id, $id);

		
		if ($farmer_kyc_data != false) {
			$response["success"] = true;
			$response["message"] = "spouse details updated successfully!";
		} else {
			$response["success"] = false;
			$response["message"] = "Failed to Add spouse details. Please try again";
		}
		echoResponse(200, $response);
	});





 ?>