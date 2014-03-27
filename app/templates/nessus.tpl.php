<script type="text/javascript">
$(function(){
  $('#submit').on('click', function(e){
    e.preventDefault();
    
    var token = $('#token').val();
    var host = $('#nessusHostname').val();
    var scan = $('#nessusScanName').val();
    
    $.ajax({
      url: '?action=nessus_scans',
      type: 'POST',
      data: ({token: token, hostname: host, scanname: scan}),
      success: function(data, status) {
          console.log(host);
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log("Details: " + desc + "\nError:" + err);
      }
    });
  });
});
 </script>

<?php
/**
 * Require the XSRF safe form
 */
require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'nessus_scans';
$form->set_name($formname);
$token = $form->get_token();
$form->save();
?>
Enter Hostname: <input type="text" name="nessusHostname" id="nessusHostname"/> Enter A Scan Name: <input type="text" name="nessusScanName" id="nessusScanName"/> 
<select id="nessusPolicy" name="nessusPolicy">
	<?php foreach ($policy as $key => $val) {?>
		<?php foreach ($val as $key1 => $val1) {?>
			<option value="<?php echo $key1;?>"><?php echo $val1["policyName"]; ?></option>
	<?php }} ?>
</select>
<input type="submit" value="Submit" name="submit" id="submit" onClick=""/><br/>
<input type="hidden" name="token" id="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
