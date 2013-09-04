<?php if(isset($message)) echo '<div id="message">' . $message . '</div>';?>

<form name="<?php echo $form_name;?>" id="<?php echo $form_name;?>" method="post" action="?action=add_hosts">
<fieldset>
<legend>Add hosts</legend>
<p>Add a batch of hosts by specifying their start and end IP.  If end IP is omitted then the
start IP will be added only.  Specify an optional host group to be applied to all the new
hosts added to the asset database.</p>
<table>
	<tr><td>Start IP:</td><td><input type="text" name="startip"/></td></tr>
	<tr><td>End IP:</td><td><input type="text" name="endip"/></td></tr>
	<tr><td>Host group:</td><td><select name="hostgroup[]" size="3" multiple="multiple">
	<?php
		foreach ($hostgroups as $key=>$val) {
			echo "<option value='$key'>$val</option>\n\t";
		}
	?>
	</select> <!-- Button to trigger modal -->
	<tr><td>Add new host group:</td><td><input type="text" name="newhostgroup"></td></tr>
	<tr><td>&nbsp;</td><td><input type="submit" value="Save changes"/></td></tr>
</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>

