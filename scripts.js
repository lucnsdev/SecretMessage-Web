
window.onbeforeunload = function() {
};

window.onload = function() {	
	var button = document.getElementById("button");
	if (button == null) return;
	button.disabled = true; 
	
	var inputText = document.getElementById("textarea_message");
	inputText.addEventListener('input', (e) => {
	    button.disabled = inputText.value.length == 0;
	});
	button.disabled = inputText.value.length == 0;
}

function storeText(message) {
    printLog("storeText");
	var url = 'save_file.php';
	var params = 'message=' + message;
	var http = new XMLHttpRequest();
	http.open('POST', url, true);
	http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function() {
		/*
		printLog("responseCode: " + http.status);
		if (http.readyState == 4 && http.status == 200) {
			printLog("responseText: " + http.responseText);
		}
		*/
	}
	http.send(params);
}

function readToken() {
}

function notifyAndroidApp(message) {
	var rawFile = new XMLHttpRequest();
    rawFile.open("GET", 'token.txt', false);
    rawFile.onreadystatechange = function () {
        if (rawFile.readyState === 4) {
            if (rawFile.status === 200 || rawFile.status == 0) {
                var destineToken = rawFile.responseText;
				
				var serverKey = 'AAAAZ2JqA4U:APA91bHVUeIhtNEUwTthsic2QyfoNiTwORNChnmFqHkoSTA79bf60-dcCoeiaDOEHCfG1Q8Q-qUHZkVozRVdrU5LCZsSknvBEE3XZW4vIRW1oa0662KlHmd9EsFQlJQZKVfyj3Lb2hhv';
				var url = 'https://fcm.googleapis.com/fcm/send';
				//var destineToken = 'fqlOMQZ4Rx6xHXxL1OfeQd:APA91bEbjeWEr77X4-BCG83cRWPYlbJhspEP9FZn74N3ROem_srSUcttqeDPdI58Chb598P11qBJdydhHh0es_W959NE11S1sPjkmlyI9pnvmJFuSICjZg_k2A1bvAPtV5R8XhV9I-mm';
				console.log("destine: " + destineToken);
				var params = '{\"to\":\"' + destineToken + '\",\"data\":{\"message\":\"' + message + '\"},\"priority\":10,\"android\":{\"priority\":\"high\"}}';
				var http = new XMLHttpRequest();
				http.open('POST', url, true);
				http.setRequestHeader('Content-type', 'application/json; charset=UTF-8');
				http.setRequestHeader('Authorization', 'key=' + serverKey);
				http.onreadystatechange = function() {
					printLog("responseCode: " + http.status);
					if (http.readyState == 4 && http.status == 200) {
						printLog("responseText: " + http.responseText);
					}
					window.location.replace("enviado.htm");
				}
				http.send(params);
            }
        }
    }
    rawFile.send(null);
    printLog("notifyAndroidApp");
}

function redirectForFirst() {
	window.location.replace("index.htm");
}

function makeAction() {
	var button = document.getElementById("button");
	button.innerText  = "Enviando...";
	button.disabled = true; 
	var inputText = document.getElementById("textarea_message");
	inputText.disabled = true; 
	notifyAndroidApp(inputText.value);
	storeText(inputText.value);	
	/*
	setTimeout(function() {
		printLog("redirect now...");
		//window.location.replace("enviado.htm");
	}, 2500);
	*/
}

function printLog(s) {
	console.log(s);
}

// Developed by @lucns
