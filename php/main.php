<?php

    require 'firebase/google_firebase.php';
	$body = file_get_contents("php://input");

    function isValidJSON($str) {
		json_decode($str);
		return json_last_error() == JSON_ERROR_NONE;
	}

	if (strlen($body) > 0) { // POST
		if (isValidJSON($body)) {
			$bodyJson = json_decode($body, true);
			
    		$firebase = new GoogleFirebase();
    		if ($firebase->expired()) {
    		    if (!$firebase->generateAccessToken()) {
			        http_response_code(401);
    		        return;
    		    }
    		}
			//$firebase->sendToDatabase($bodyJson);    		
    		if ($firebase->sendMessage($bodyJson)) http_response_code(204);
            else http_response_code(401);
			return;
	    }
    	http_response_code(401);
	} else { // GET
    	http_response_code(204);
	}
?>