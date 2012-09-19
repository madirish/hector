var xmlhttp;

function getPage(url, method, formName) {
	var strings = null;
	if (method == null) {
		method = "GET";
	}
	xmlhttp = getXmlHttpObject();
	if (xmlhttp == null) {
		alert("Your browser cannot handle AJAX requests required by this application");
		return false;
	}
	if (url.search(/\?/) < 0) {
		url += "?ajax=yes";
	}
	else {
		url += "&ajax=yes";
	}
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open(method,url,true);
	if (method == "POST") {
		xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		theForm = document.getElementById(formName);
		for (i=0;i<theForm.elements.length;i++) {
			// We have to make a special exemption for checkboxes so we only get the checked ones
			if (theForm.elements[i].type == "checkbox" && theForm.elements[i].checked) {
				if (strings == null) {
					strings = theForm.elements[i].name + "=" + theForm.elements[i].value;
				}
				else {
					strings += "&" + theForm.elements[i].name + "=" + theForm.elements[i].value;
				}
			}
			else if (theForm.elements[i].type != "checkbox") {
				if (strings == null) {
					strings = theForm.elements[i].name + "=" + theForm.elements[i].value;
				}
				else {
					strings += "&" + theForm.elements[i].name + "=" + theForm.elements[i].value;
				}
			}
		}
	}
	xmlhttp.send(strings);
}

function stateChanged() {
	if (xmlhttp.readyState == 4) {
		document.getElementById("content").innerHTML = xmlhttp.responseText;
	}
}

function getXmlHttpObject() {
	if (window.XMLHttpRequest) {
		return new XMLHttpRequest();
	}
	if (window.ActiveXObject) {
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	return null;
}