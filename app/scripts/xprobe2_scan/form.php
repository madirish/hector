<!-- xprobe2 form -->
<?php global $generic; ?>
<script type="text/javascript">
$(document).ready(function () {
	// Set the scan name in the form from parent template
	$('#add_scan_type_form #script').val('xprobe2_scan.php');
	
	// User input validation 
	
    <?php
        // This is JavaScript for the edit form
        if(is_object($generic)) {
    ?>
    $('#name').val('<?php echo $generic->get_name();?>');
    var flags = '<?php echo $generic->get_flags();?>';
    <?php } ?>
    
    $('#saveScan').bind('click', function() {
        if ($('#name').val() == '') {
            $('#name').addClass('text-error');
            alert("You must supply a script name.");
        }
        else {
            $('#add_scan_type_form').submit();
        }
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
	<legend>Xprobe2 Scan</legend>
	<p>Configure your Xprobe2 (<a href="http://sourceforge.net/projects/xprobe/">http://sourceforge.net/projects/xprobe//</a>) scan 
	to perform operating system version detection.</p>
    <?php if (! file_exists($_SESSION['xprobe2_exec_path'])) { ?>
    <p><strong>Xprobe2 not found!</strong></p>
    <p>It appears that xprobe2 is not installed on your system or the path to the executable is incorrect in your config.ini.</p>
    <?php } else { ?> 
	<p>Xprobe2 uses port data from the database to determine operating systems and update host records.</p>
	<p>xprobe2  is  an  active  operating system fingerprinting tool with a different approach to operating system
	fingerprinting. xprobe2 relies on fuzzy signature matching, probabilistic guesses, multiple matches  simul-
	taneously, and a signature database.</p>
	<p>The  operation  of  xprobe2  is  described in a paper released at DefCon 10 titled 
    <a href="http://fossies.org/linux/xprobe2/docs/xprobe2-defcon10.pdf">A 'Fuzzy' Approach to Remote Active Operating System Fingerprinting</a>.</p>
		<div class="control-group">
        <label class="control-label" for="name"><strong>Scan name:</strong></label>
        <div class="controls">
            <input type="text" id="name" name="name" class="input-block-level input-xxlarge" placeholder="Descriptive scan name"/>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <input type="button" id="saveScan" value="Save Scan"/>
        </div>
    </div>
     <?php } ?>
</fieldset>