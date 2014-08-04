<!-- ncrack form -->
<?php global $generic;?>
<script type="text/javascript">
$(document).ready(function () {
	// Set the scan name in the form from parent template
	$('#add_scan_type_form #script').val('ncrack_scan.php');
	
	
	// User input validation for connection delay
	$('.connectDelay').bind('change', function() {
		if ($(this).val().replace(/[^\d^]/g) !== $(this).val()) {
			alert("Error in " +$(this).attr('name') + ": Only numbers are allowed.");
			$(this).parent().addClass('error');
		}
	});
	
	
	// Logic to compose flags from input fields
	$('#saveScan').bind('click', function() {
        if ($("#name").val() == '') {
            $("#name").addClass('error');
        	alert("Please enter a scan name");
            $("#name").focus();
            return false;
        }
		var flags = '';
        $("#tcpPortList").val('');
        
        if ($('#sshcheckbox').is(':checked')) {
        	$("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'ssh';
            });
        }
        if ($('#ftpcheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'ftp';
            });
        }
        if ($('#telnetcheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'telnet';
            });
        }
        if ($('#httpcheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'http';
            });
        }
        if ($('#smbcheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'smb';
            });
        }
        if ($('#pop3checkbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'pop3';
            });
        }
        if ($('#pop3scheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'pop3s';
            });
        }
        if ($('#httpscheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'https';
            });
        }
        if ($('#rdpcheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'rdp';
            });
        }
        if ($('#vnccheckbox').is(':checked')) {
            $("#tcpPortList").val(function(i,val) {
                return val + (val ? ',' : '') + 'vnc';
            });
        }
        
        
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
		<div class="control-group">
            <label class="control-label" for="name"><strong>Scan name:</strong></label>
            <div class="controls">
                <input type="text" id="name" name="name" class="input-block-level" placeholder="Descriptive scan name"/>
            </div>
        </div>
		<div class="control-group">
			<label for="connectDelay"><strong>Connect delay (secs):</strong></label>
			<div class="controls">
                <input name="Connection delay" type="text" id="connectDelay" value="" class="input-block-level" placeholder="ex: 2"/>
            </div>
		</div>
		<div class="conrtol-group">
            <label class="control-label"><strong>Specifications:</strong></label>
			<div class="controls">
                <input type="checkbox" name="serviceboxes" id="sshcheckbox" value="ssh">SSH<br/>
                <input type="checkbox" name="serviceboxes" id="ftpcheckbox" value="ftp">FTP<br/>
                <input type="checkbox" name="serviceboxes" id="telnetcheckbox" value="telnet">Telnet<br/>
                <input type="checkbox" name="serviceboxes" id="httpcheckbox" value="http">HTTP<br/>
                <input type="checkbox" name="serviceboxes" id="smvcheckbox" value="smb">SMB<br/>
                <input type="checkbox" name="serviceboxes" id="pop3checkbox" value="pop3">POP3<br/>
                <input type="checkbox" name="serviceboxes" id="pop3scheckbox" value="pop3s">POP3s<br/>
                <input type="checkbox" name="serviceboxes" id="httpscheckbox" value="https">HTTPS<br/>
                <input type="checkbox" name="serviceboxes" id="rdpcheckbox" value="rdp">RDP<br/>
                <input type="checkbox" name="serviceboxes" id="vnccheckbox" value="vnc">VNC<br/>
            </div>
        </div>
        <div class="control-group">
            <label for="saveScan">&nbsp;</label>
            <div class="controls">
                <input type="button" id="saveScan" value="Save Scan" class="btn"/>
            </div>
        </div>
        <input name="TCP port list" type="hidden" id="tcpPortList" value="" class="input-block-level portnumber"/>
     <?php } ?>
</fieldset>