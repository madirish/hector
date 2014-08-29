<!-- Nmap version can form -->

<script type="text/javascript">
$(document).ready(function () {
	// Set the scan name in the form from parent template
	$('#add_scan_type_form #script').val('nmap_version_scan.php');
	
	// User input validation 
	$('#saveScan').bind('click', function() {
		$('#add_scan_type_form').submit();
	});
    <?php
        // This is JavaScript for the edit form
        if(is_object($generic)) {
    ?>
    $('#name').val('<?php echo $generic->get_name();?>');
    var flags = '<?php echo $generic->get_flags();?>';
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
	<legend>Nmap Version Scan</legend>
	<p>This scan independently performs a version scan using the existing database of detected ports.</p>
		<table>
		<tr><td><strong>Scan name:</strong></td><td><input type="text" id="name" name="name" class="input-block-level" placeholder="Descriptive scan name"/></td></tr>
		<tr><td>&nbsp;</td><td><input type="button" id="saveScan" value="Save Scan"/></td></tr>
		</table>
</fieldset>