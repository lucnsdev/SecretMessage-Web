<?php

    class GoogleFirebase {
    
    	private $authDataFilePath;
    	
    	public function __construct() {
            $this->authDataFilePath = __DIR__ . "/data/auth_data.json";
        }
    	
    	function base64UrlEncode($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }
        
        function signJWT($header, $payload, $privateKeyPem) {
            $data = $header . '.' . $payload;
            $signature = '';
            openssl_sign($data, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
            return $this->base64UrlEncode($signature);
        }
    	
    	function generateAccessToken() {
    	    $header = $this->base64UrlEncode(json_encode([
                "alg" => "RS256",
                "typ" => "JWT"
            ]));
            
    		$timestamp = time();
            $payload = $this->base64UrlEncode(json_encode([
                "iss" => "firebase-adminsdk-fbsvc@esp32-firebase-5a830.iam.gserviceaccount.com",
                "sub" => "firebase-adminsdk-fbsvc@esp32-firebase-5a830.iam.gserviceaccount.com",
    			"aud" => "https://oauth2.googleapis.com/token",
    			"iat" => $timestamp,
    			"exp" => $timestamp + 3600,
    			"scope" => "https://www.googleapis.com/auth/devstorage.full_control https://www.googleapis.com/auth/datastore https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/iam"
            ]));
            
            $privateKey = file_get_contents(__DIR__ . "/data/private_key.pem");
            $signature = $this->signJWT($header, $payload, $privateKey);
    	    
    	    $jsonBody = json_encode([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $header . '.' . $payload . '.' . $signature
            ]);
    		/*
            echo "PrivateKey:" . $privateKey . "\n";
            echo "Header:" . $header . "\n";
            echo "Payload:" . $payload . "\n";
            echo "Signature:" . $signature . "\n";
            */
            
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonBody)
            ]);
            
            $response = curl_exec($ch);
    		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		//echo "Response code: " . $response_code . "\n";
    		//echo "Response: " . $response . "\n";
            curl_close($ch);
            
            if ($response_code == 200) {
                $jsonData = json_decode($response);
                $jsonData->expirate_at = $timestamp + 3600;
                file_put_contents($this->authDataFilePath, json_encode($jsonData));
                return true;
            }
            return false;
    	}
    	
    	function getAccessToken() {
            $jsonString = file_get_contents($this->authDataFilePath);
            $data = json_decode($jsonString, true);
            return $data["access_token"];
    	}
    	
        function expired() {
            if (!file_exists($this->authDataFilePath)) return true;
            $jsonString = file_get_contents($this->authDataFilePath);
            $data = json_decode($jsonString, true);
            return $data["expirate_at"] <= time();
        }
        
        function sendMessage($data) {
            $data = $this->convertAllToString($data);
            $destineToken = file_get_contents(__DIR__ . "/data/destine_register_id.txt");
            $message = [
                "message" => [
                    "data" => $data,
                    "token" => $destineToken,
                    "android" => ["priority" => "HIGH", "ttl" => "60s"]
                ]
            ];
            $jsonBody = json_encode($message);
            
            $ch = curl_init('https://fcm.googleapis.com/v1/projects/esp32-firebase-5a830/messages:send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonBody)
            ]);
            
            $response = curl_exec($ch);
    		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		//echo "Response code: " . $response_code . "\n";
    		//echo "Response: " . $response . "\n";
            curl_close($ch);
            
            return $response_code == 200;
        }
        
        function sendToDatabase($data) {
            $data = json_encode($data);
            $ch = curl_init('https://mysamples-4f48d-default-rtdb.firebaseio.com//tracker/data.json?auth=' . $this->getAccessToken());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ]);
            
            $response = curl_exec($ch);
    		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		//echo "Response code: " . $response_code . "\n";
    		//echo "Response: " . $response . "\n";
            curl_close($ch);
            
            return $response_code == 200;
        }

        function convertAllToString($data) {
            $out = [];
            foreach ($data as $key => $value) {
                if (is_array($valor)) {
                    $out[$key] = convertAllToString($value);
                } elseif (is_bool($value)) {
                    $out[$key] = $value ? 'true' : 'false';
                } elseif (is_null($value)) {
                    $out[$key] = 'null';
                } else {
                    $out[$key] = (string) $value;
                }
            }
            return $out;
        }
    }
?>