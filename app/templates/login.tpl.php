<form method="post" action="?action=login_scr" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
<fieldset>
	<legend>Please log in</legend>
	<table>
		<tr><td>Username:</td><td><input type="text" name="username"/></td></tr>
		<tr><td>Password:</td><td><input type="password" name="password"/></td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="Log in"/></td></tr>
	</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
<?php
	if (isset($_GET['sorry']) || isset($sorry)) {
		echo '<div id="sorry">Sorry, unrecognized credentials.</div>';
	}
?>
