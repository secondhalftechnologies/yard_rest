<?php 
	
	/**
	 *  Add new Farmer to the data base with Post method
	 *  The following post method will create a farmer row in data base and will return created farmer id
	 */
	$app->post('/farmers', 'authenticate', function() use ($app){
		verifyRequiredParams([ 'tbl_name', 'tbl_aadhaar', 'tbl_gender']); //provide a list of required parametes
		$response = [];
		$farmer_data = $app->request->post(); //fetching the post data into variable
		
		// ------------------------
		// Do Validation here
		// ------------------------

		$db = new DbHandler();
		// creating new farmer
		$farmer_id = 23; //$db->createFarmer($farmer_data);
		if ($farmer_id != NULL) {
			$response["success"] = true;
			$response["message"] = "Farmer created successfully";
			$response["farmer_id"] = $farmer_id;
		} else {
			$response["success"] = false;
			$response["message"] = "Failed to Add Farmer. Please try again";
		}
		echoResponse(201, $response);
	});


	/**
	 * Get one or All farmers
	 * It is just an example you will have to get the current agent_id then list his farmer list
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->get('/farmers/:id', 'authenticate', function($id = null) use ($app) {

		$response = [];

		global $user_id;
		$db = new DbHandler();

		//will have to fetch data from database
		//eg. select * from farmes where farmer_id = [id]
		$farmers = $db->getFarmers($user_id, 0, 1, $id);


		$response["success"] = true;
		$response["data"] = $farmers;

		echoResponse(200, $response);
	});


	/**
	 * Get one or All farmers
	 * It is just an example you will have to get the current agent_id then list his farmer list
	 * 'authenticate' parameter is a function in Utils.php file where we are checking authorization 
	 */
	$app->get('/farmers(/:limit/:offset)', 'authenticate', function($limit = 0, $offset = 10) use ($app) {

		$response = [];

		global $user_id;
		$db = new DbHandler();

		
		//will have to fetch data from database
		//eg. select * from farmes
		$farmers = $db->getFarmers($user_id, $limit, $offset);


		$response["success"] = true;
		$response["data"] = $farmers;

		echoResponse(200, $response);
	});

 ?>