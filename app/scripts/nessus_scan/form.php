<!-- nessus form -->
<script type="text/javascript">
	
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
	<legend>Nessus Scan</legend>
	<p>Configure your Nessus scan to perform patch, configuration, and compliance auditing.</p>
	
		<table>
		<tr><td><strong>Scan name:</strong></td><td><input type="text" id="name" name="name" class="input-block-level" placeholder="Descriptive scan name"/></td></tr>
		<tr><td style="vertical-align: text-top;"><strong>Specifications:<strong></td><td>
			<div class="form-group">
			<div class="control-group">
				<label for="scanPolicy">Scan Policy:</label>
				<select id="nessusPolicy" name="nessusPolicy">
					<?php foreach ($policy as $key => $val) {?>
						<?php foreach ($val as $key1 => $val1) {?>
							<option value="<?php echo $key1;?>"><?php echo $val1["policyName"]; ?></option>
					<?php }} ?>
				</select>
			</div>
			<div class="control-group">
				<label for="scanHostname">Hostname:</label>
				<input name="Scan Hostname" type="text" id="scanHostname" value="" class="input-block-level" placeholder="ex: infosec.sas.upenn.edu"/>
			</div>
			</div>
		</td></tr>
		<tr><td>&nbsp;</td><td><input type="button" id="saveScan" value="Save Scan"/></td></tr>
		</table>
</fieldset>