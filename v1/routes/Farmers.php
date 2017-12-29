<?php 
	// $db     = new DbHandler();
	require_once '../include/DbConnect.php';
        // opening db connection
	$db_con = new DbConnect();
	$conn   = $db_con->connect();
	
	/**
	 *  Add new Farmer to the data base with Post method
	 *  The following post method will create a farmer row in data base and will return created farmer id
	 */
	$app->post('/farmers', 'authenticate', function() use ($app, $conn){
		// verifyRequiredParams([ 'tbl_name', 'tbl_aadhaar', 'tbl_gender']); //provide a list of required parametes
		$response = [];
		$farmer_data = $app->request->post(); //fetching the post data into variable
		
		// ------------------------
		// Do Validation here
		// ------------------------

		// creating new farmer

		$fm_status = '1';
		$date      = new DateTime(null, new DateTimeZone('Asia/Kolkata'));
		$datetime  = $date->format('Y-m-d H:i:s');

		$stmt_get_fm_id = $conn->prepare(" select fm_id from tbl_farmers order by id desc limit 0,1 ");
		$res_get_fm_id = $stmt_get_fm_id->execute();
		$fm_data = $stmt->get_result()->fetch_assoc();

		$fm_id = $fm_data['fm_id'];

		if($fm_id == '')
		{
			$fm_id	= 100000;
		}
		else
		{
			$fm_id	= $fm_id + 1;
		}

		// insert query
		$stmt      = $conn->prepare(" INSERT INTO `tbl_farmers`(`fm_caid`, `fm_id`, `fm_name`, `fm_aadhar`, `fm_mobileno`, `fm_status`, `fm_createddt`, `fm_createdby`) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ");
		$stmt->bind_param("ssssssss", $farmer_data['fm_caid'], $fm_id, $farmer_data['txt_name'], $farmer_data['fm_aadhar'], $farmer_data['fm_mobileno'], $fm_status, $datetime, $farmer_data['fm_caid']);
		$result    = $stmt->execute();
		$farmer_id = $stmt->insert_id;
        $stmt->close();
        // Check for successful insertion
        if ($result) {
			echoResponse(201, $farmer_id);
            exit();

			$farmer_id = $db->createFarmer($farmer_data); //23
			if ($farmer_id != NULL) {
				$response["success"] = true;
				$response["message"] = "Farmer created successfully";
				$response["farmer_id"] = $farmer_id;
			} else {
				$response["success"] = false;
				$response["message"] = "Failed to Add Farmer. Please try again";
			}
        }
        else
        {
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