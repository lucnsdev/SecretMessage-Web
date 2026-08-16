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
            date_default_timezone_set('America/Fortaleza');
			$message = [];
            $message[date("YmdHis")] = $bodyJson["message"];
    		if ($firebase->databasepUpdate($message) && $firebase->sendMessage($bodyJson)) {
                http_response_code(204);
            } else {
                http_response_code(401);
            }
			return;
	    }
    	http_response_code(401);
	} else { // GET    
        $content = file_get_contents("../not_found.htm");
        echo $content;
    	http_response_code(200);
	}
?>