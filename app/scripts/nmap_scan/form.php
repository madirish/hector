<script type="text/javascript">
$(document).ready(function () {
	// Set the scan name in the form from parent template
	$('#script').val('nmap_scan.php');
	// User input validation
	$('.portnumber').bind('change', function() {
		if ($(this).val().replace(/[^\d^,^\-]/g) !== $(this).val()) {
			alert("Error in " +$(this).attr('name') + ": Only numbers, commas, and dashes are allowed.");
			$(this).parent().addClass('error');
		}
		if ($(this).val().match(/-/g) !== null) {
			if ($(this).val().match(/\d-\d/) === null) {
				alert("Negative port numbers are invalid.");
				$(this).parent().addClass('error');
			}
		}
		var ports = $(this).val().split(/[,\-]/);
		for (var port in ports) {
			if (ports[port] > 65535) {
				alert("Invalid port number " + ports[port]);
				$(this).parent().addClass('error');
			}
		}
	});
	// Logic to compose flags from input fields
	$('#saveScan').bind('click', function() {
		var flags = '';
		if ( $("#alertChanges").is(':checked') == true) {
			flags += ' -a ';
		}
		if ( $("#versionDetection").is(':checked') == true) {
			flags += ' -v ';
		}
		if ( $("#openTcpPortList").val() !== '') {
			flags += ' -e=' + $("#openTcpPortList").val() + ' ';
		}
		if ( $("#openUdpPortList").val() !== '') {
			flags += ' -u=' + $("#openUdpPortList").val() + ' ';
		}
		if ( $("#tcpPortList").val() !== '' || $("#udpPortList").val() !== '') {
			flags += ' -p=';
		}
		if ( $("#tcpPortList").val() !== '' ) {
			flags += 'T:' + $("#tcpPortList").val();
		}
		if ( $("#udpPortList").val() !== '' ) {
			if ( $("#tcpPortList").val() !== '' ) {
				flags += ',';
			}
			flags += 'U:' + $("#udpPortList").val();
		}
		$('#flags').val(flags);
		$('#add_scan_type_form').submit();
	});
})
</script>

<?php

/**
 * Require the XSRF safe form
 */
require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'add_scan_type_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();
?>
<fieldset>
	<legend>NMAP Scan</legend>
	<p>Configure your NMAP (Network Mapper) scan to detect open ports on your hosts.</p>
	
		<table>
		<tr><td><strong>Scan name:</strong></td><td><input type="text" name="name" class="input-block-level" placeholder="Descriptive scan name"/></td></tr>
		<tr><td style="vertical-align: text-top;"><strong>Specifications:<strong></td><td>
			<div class="form-group">
			<label>Alert on changes? <input type="checkbox" id="alertChanges" /></label>
			<label>Attempt version detection? <input type="checkbox" id="versionDetection" /></label>
			<div class="control-group">
				<label for="tcpPortList">TCP ports:</label>
				<input name="TCP port list" type="text" id="tcpPortList" value="" class="input-block-level portnumber" placeholder="ex: 2,3,5-9,12"/>
			</div>
			<div class="control-group">
				<label for="udpPortList">UDP ports:</label>
				<input name="UDP port list" type="text" id="udpPortList" value="" class="input-block-level portnumber" placeholder="ex: 2,3,5-9,12"/>
			</div>
			<div class="control-group">
				<label for="openTcpPortList">Only scan hosts with the following known TCP ports:</label>
				<input name="Existing TCP port list" type="text" id="openTcpPortList" value="" class="input-block-level portnumber" placeholder="ex: 2,3,5-9,12"/>
			</div>
			<div class="control-group">
				<label for="openUdpPortList">Only scan hosts with the following known UDP ports:</label>
				<input name="Existing UDP port list" type="text" id="openUdpPortList" value="" class="input-block-level portnumber" placeholder="ex: 2,3,5-9,12"/>
			</div>
			</div>
		</td></tr>
		<tr><td>&nbsp;</td><td><input type="button" id="saveScan" value="Save Scan"/></td></tr>
		</table>
</fieldset>