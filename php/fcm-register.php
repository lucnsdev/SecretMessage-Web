<?php
		
	if (isset($_POST["action"])) {
		$dataFromAndroid = $_POST["data"];		
		$filePath = "register" . ".txt";
		$fileOpen = fopen($filePath, "w");
		fwrite($fileOpen, $dataFromAndroid);
		fclose($fileOpen);
	} else {
		$redirectTo = "../index.htm";
		echo "<META HTTP-EQUIV=REFRESH CONTENT=1;".$redirectTo.">";
	}
	
?>
