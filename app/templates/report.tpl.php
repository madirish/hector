<script type="text/javascript" src="js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<div id="content">


<?php
	if (! isset($content)) {
?>
<ol>
	<li><a href="?action=reports&report=by_port">Ports detected</a></li>
	<li><a href="?action=reports&report=danger_host">Dangerous host list</a></li>
	<li><a href="?action=reports&report=nonisuswebservers">Web servers (excluding WKS managed or printers)</a></li>
<?php	
	}
else {
	if ($content == '') {
?>
<form method="post" action="?action=reports&report=by_port" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
<fieldset>
	<legend>Find hosts with:</legend>
	<table>
		<tr><td>Any of these ports:</td><td><input type="text" name="anyports"/> (comma separated list)</td></tr>
		<tr><td>All of these ports:</td><td><input type="text" name="allports"/> (comma separatedlist)</td></tr>
		<tr><td>None of these ports:</td><td><input type="text" name="portsex"/> (comma separatedlist)</td></tr>
		<tr><td>All of these tags:</td><td><select name="tagsin" size="4">
			<?php foreach ($tags as $tag) echo '<option value="' . $tag->get_id() . '">' . $tag->get_name() . '</option>'; ?>
			</select> (only search for machines with these tags)</td></tr>
		<tr><td>None of these Tags:</td><td><select name="tagsex" size="4">
			<?php foreach ($tags as $tag) echo '<option value="' . $tag->get_id() . '">' . $tag->get_name() . '</option>'; ?>
			</select> (do not report machines with these tags)</td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="Search"/></td></tr>
	</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
		
<?php		
	}
	else {
		echo $content;	
	}
}
?>
</div>