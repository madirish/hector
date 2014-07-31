<!-- ncrack form -->
<?php global $generic;?>
<script type="text/javascript">
$(document).ready(function () {
	// Set the scan name in the form from parent template
	$('#add_scan_type_form #script').val('ncrack_scan.php');
	
	// User input validation fpr portnumbers
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
	
	// User input validation for connection delay
	$('.connectDelay').bind('change', function() {
		if ($(this).val().replace(/[^\d^]/g) !== $(this).val()) {
			alert("Error in " +$(this).attr('name') + ": Only numbers are allowed.");
			$(this).parent().addClass('error');
		}
	});
	
	
	// Logic to compose flags from input fields
	$('#saveScan').bind('click', function() {
		var flags = '';
		if ( $("#tcpPortList").val() !== '' ) {
			flags += '-p=' + $("#tcpPortList").val();
		}
		if ( $("#connectDelay").val() !== '' ) {
			flags += ' -d=' + $("#connectDelay").val();
		}
		$('#flags').val(flags);
		$('#add_scan_type_form').submit();
	});
    <?php
        // This is JavaScript for the edit form
        if(is_object($generic)) {
    ?>
    $('#name').val('<?php echo $generic->get_name();?>');
    var flags = '<?php echo $generic->get_flags();?>';

    if (flags.search('-p') > -1) {
        var portString = flags.substring(flags.search('-p=') + 3);
        $('#tcpPortList').val(portString.split(" ")[0]);
    }
    if (flags.search('-d') > -1) {
        var connectDelayString = flags.substring(flags.search('-d=') + 3);
        $('#connectDelay').val(connectDelayString.split(" ")[0]);
    }
    <?php } ?>
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
	<legend>Ncrack Scan</legend>
	<p>Configure your Ncrack (<a href="http://nmap.org/ncrack/">http://nmap.org/ncrack/</a>) scan 
	to perform network based brute force attacks.</p>
	<p> Protocols supported include RDP, SSH, http(s), SMB, pop3(s), VNC, FTP, and telnet.</p>
	<p>Usernames and passwords are pulled from 10 most common usernames and passwords attempted
	by attackers using Kojoney2 data in HECTOR.</p>
    <?php if (! is_executable($_SESSION['ncrack_exec_path'])) { ?> 
    <p><strong>Ncrack not found!</strong></p>
    <p>Either Ncrack is not installed on your system or you need to update your config.ini to point to it.</p>
    <?php } else { ?> 
		<table>
		<tr><td><strong>Scan name:</strong></td><td><input type="text" id="name" name="name" class="input-block-level" placeholder="Descriptive scan name"/></td></tr>
		<tr><td style="vertical-align: text-top;"><strong>Specifications:<strong></td><td>
			<div class="form-group">
			<div class="control-group">
				<label for="tcpPortList">TCP ports to scan (comma delimited):</label>
				<input name="TCP port list" type="text" id="tcpPortList" value="" class="input-block-level portnumber" placeholder="ex: 2,3,5-9,12"/>
			</div>
			<div class="control-group">
				<label for="connectDelay">Connection delay (seconds):</label>
				<input name="Connection delay" type="text" id="connectDelay" value="" class="input-block-level connectDelay" placeholder="ex: 2"/>
			</div>
			</div>
		</td></tr>
		<tr><td>&nbsp;</td><td><input type="button" id="saveScan" value="Save Scan"/></td></tr>
		</table>
     <?php } ?>
</fieldset>