<!-- xprobe2 form -->

<script type="text/javascript">
$(document).ready(function () {
	// Set the scan name in the form from parent template
	$('#script').val('xprobe2_scan.php');
	
	// User input validation 
	
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
	<legend>Nikto Scan</legend>
	<p>Configure your Xprobe2 (<a href="http://sourceforge.net/projects/xprobe/">http://sourceforge.net/projects/xprobe//</a>) scan 
	to perform operating system version detection.</p>
	<p>Xprobe2 uses port data from the database to determine operating systems and update host records.</p>
	<p>xprobe2  is  an  active  operating system fingerprinting tool with a different approach to operating system
	fingerprinting. xprobe2 relies on fuzzy signature matching, probabilistic guesses, multiple matches  simul-
	taneously, and a signature database.</p>
	<p>The  operation  of  xprobe2  is  described in a paper titled \"xprobe2 - A ´Fuzzy´ Approach to Remote Active";
	Operating System Fingerprinting\".</p>
		<table>
		<tr><td><strong>Scan name:</strong></td><td><input type="text" id="name" name="name" class="input-block-level" placeholder="Descriptive scan name"/></td></tr>
		<tr><td>&nbsp;</td><td><input type="button" id="saveScan" value="Save Scan"/></td></tr>
		</table>
</fieldset>