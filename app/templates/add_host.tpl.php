<form name="<?php echo $form_name;?>" id="<?php echo $form_name;?>">
<fieldset>
<legend>Add host</legend>
<table>
	<tr><td>IP:</td><td><input type="text" name="ip"/></td></tr>
	<tr><td>Hostname:</td><td><input type="text" name="hostname"/></td></tr>
	<tr><td>&nbsp;</td><td><input type="button" name="submit" value="Save changes" onClick='javascript:if (checkAHForm()) {getPage("?action=edit_host_scr", "POST", "<?php echo $form_name;?>");}'/></td></tr>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>