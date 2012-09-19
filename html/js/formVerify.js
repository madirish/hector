
function checkAHForm() {
	theForm = document.add_host_form;
	if (theForm && theForm.ip.value == '' && theForm.hostname.value == '') {
		alert('Hostname or IP must be supplied.');
		return false;
	}
	else {
		return true;
	}
}