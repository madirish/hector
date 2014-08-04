<!-- screenshot form -->
<?php global $generic; ?>
<script type="text/javascript">
$(document).ready(function () {
    // Set the scan name in the form from parent template
    $('#add_scan_type_form #script').val('screenshot_scan.php');
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
    <legend>Screenshot Scan</legend>
    <p>Screenshot scanning utilizes the <a href="http://phantomjs.org/">PhantomJS</a> libraries to browse all URLs for hosts that have URLs as
    detected by a BingFQDN scan or have open ports 80, 443 or 8080.</p>
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
</fieldset>