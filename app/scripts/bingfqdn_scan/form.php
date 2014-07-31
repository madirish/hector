<!-- Bing search API form -->
<?php global $generic; ?>
<script type="text/javascript">
$(document).ready(function () {
    // Set the scan name in the form from parent template
    $('#add_scan_type_form #script').val('bingfqdn_scan.php');
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
    <legend>Bing API Scan</legend>
    <p>Utilizes the Bing API to search for all domain names associated with IP addresses that have web server
    ports tracked in the HECTOR database.</p>
    <?php if ($_SESSION['bing_api_key'] == 'your_bing_api_key') { ?>
    <p><strong>There does not appear to be a valid Bing API key!</strong></p>
    <p>For information about setting up a Bing account to use the API 
    please see (<a href="https://datamarket.azure.com/about">https://datamarket.azure.com/about</a>) 
    then update the config.ini with your key.</p>
    <?php } else { ?>
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