
window.onbeforeunload = function () {
};

window.onload = function () {
	var button = document.getElementById("button");
	var buttonResend = document.getElementById("button_resend");
	var textTitle = document.getElementById("title");
	var textCounter = document.getElementById("text_counter");
	if (button != null) {
		button.disabled = true;
		var inputText = document.getElementById("textarea_message");
		inputText.addEventListener('input', (e) => {
			button.disabled = inputText.value.length == 0;
			textCounter.innerText = inputText.value.length + "/1024";
		});
		button.disabled = inputText.value.length == 0;
		button.addEventListener('click', (e) => {
			makeAction();
		});
	}
	if (buttonResend != null) {
		buttonResend.addEventListener('click', (e) => {
			console.log("resend click");
			buttonResend.disabled = true;
			window.location.replace("index.htm");
		});
		setTimeout(function () {
			textTitle.innerText = "Mensagem enviada e recebida!";
		}, 1000);
	}

	document.addEventListener("keydown", (event) => {
		const keyName = event.key;
		if (keyName == "Enter") {
			makeAction();
		}
	});
}

function makeAction() {
	var button = document.getElementById("button");
	button.innerText = "Enviando...";
	button.disabled = true;
	var inputText = document.getElementById("textarea_message");
	inputText.disabled = true;

	var params = '{\"message\":\"' + inputText.value + '\"}';
	var http = new XMLHttpRequest();
	http.open('POST', "../php/main.php", true);
	http.onreadystatechange = function () {
		console.log("responseCode: " + http.status);
		console.log("responseText: " + http.responseText);
		if (http.readyState == 4 && http.status == 204) {
			//window.location.replace("sent.htm");
		}
	}
	http.send(params);
	printLog("notifyAndroidApp");
}
