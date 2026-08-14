<?php
	
	if (isset($_POST["message"])) {
		saveFile($_POST["message"]);
	} else {		
		echo "<html><body><h1>404 Not found</h1></body></html>";
	}
	
	function saveFile($message) {
		$folderPath = "messages";
		if(!file_exists($folderPath) || !is_dir($folderPath)) {
			mkdir($folderPath, 0755);
		}
		date_default_timezone_set('America/Sao_Paulo');
		$now = date("D M j G:i:s T Y");
		$filePath = "messages/" . $now . ".txt";
		$fileOpen = fopen($filePath, "w");
		fwrite($fileOpen, $message);
		fclose($fileOpen);
	}

?>