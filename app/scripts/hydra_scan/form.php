<!-- hydra form -->
<script type="text/javascript">
$(document).ready(function () {
    // Set the scan name in the form from parent template
    $('#script').val('ncrack_scan.php');
  
    
    
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
if (! isset($_SESSION['hydra_exec_path']) || ! is_executable($_SESSION['hydra_exec_path'])) {
    echo "<p><a href='https://www.thc.org/thc-hydra/'>Hydra</a> doesn't seem to be installed on your system.</p>";
}
else {
?>
<fieldset>
    <legend>Hydra Brute Force Utility</legend>
    <p>Configure your Hydra (<a href="https://www.thc.org/thc-hydra/">https://www.thc.org/thc-hydra//</a>) scan 
    to perform network based brute force attacks.</p>
    <p> Hydra is a time tested, robust, credential guessing utility that can attack a number of protocols.</p>
    <p> Hydra uses username and password lists generated from HECTOR's honeypot data (from Kojoney2).</p>
        <table>
        <tr><td><strong>Scan name:</strong></td><td><input type="text" id="name" name="name" class="input-block-level" placeholder="Descriptive scan name"/></td></tr>
        <tr><td style="vertical-align: text-top;"><strong>Specifications:<strong></td><td>
            <div class="form-group">
            <div class="control-group">
                <label>FTP <input type="checkbox" id="ftp" /></label>
                <label>MySQL <input type="checkbox" id="mysql" /></label>
                <label>SMB <input type="checkbox" id="smb" /></label>
                <label>SSH <input type="checkbox" id="ssh" /></label>
                <label>Telnet <input type="checkbox" id="telnet" /></label>
                <label>VNC <input type="checkbox" id="vnc" /></label>
            </div>
            </div>
        </td></tr>
        <tr><td>&nbsp;</td><td><input type="button" id="saveScan" value="Save Scan"/></td></tr>
        </table>
</fieldset>
<?php } ?>