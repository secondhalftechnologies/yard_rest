<?php
require '../vendor/autoload.php';
require_once 'Config.php';
if(PHP_DEBUG_MODE){
    error_reporting(-1);
    ini_set('display_errors', 'On');
}
// authorized user id from db - global var
$user_id = NULL;



/**
* Verifying required params posted or not
*/
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || !is_array($request_params)) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
    if ($error) {
        // required fields are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response['success'] = false;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}



/**
* Valiedating email address
*/
function validateEmail($email){
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['message'] = 'Email is not valid';
        echoResponse(400, $response);
        $app->stop();
    }
}



/**
* Echo json response
* @param String $status_code http response code
* @param Int $response Json response
*/
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
    // setting response content type to json
    $app->contentType('application/json');
    echo json_encode($response);
}



/**
*   Adding Middle Layer to authenticate every request
*   Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {

    // getting request header
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    // verifying authorization header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();
        // get the api key
        $token = $headers['Authorization'];
        // validating api key
        if (!$db->isValidToken($token)) {
            //api key is not present in users table
            $response['success'] = false;
            $response['message'] = 'Access denied. Invalid api token';
            echoResponse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user = $db->getUserId($token);
            if ($user != NULL) {
                $user_id = $user['id'];
            }
        }
    } else {
        // api key is missing in header
        $response['success'] = false;
        $response['message'] = "API Token is missing";
        echoResponse(400, $response);
        $app->stop();
    }



}
?>