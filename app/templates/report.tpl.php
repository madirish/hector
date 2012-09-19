<script type="text/javascript" src="js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<div id="content">

<h2><?php echo $title;?></h2>

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
	<legend>What ports are you interested in?</legend>
	<table>
		<tr><td>Ports:</td><td><input type="text" name="ports"/> (comma separated list of ports open on machine to search for)</td></tr>
		<tr><td>Exclude ports:</td><td><input type="text" name="portsex"/> (comma separated, if these ports are open exlude from resuts)</td></tr>
		<tr><td>With tags:</td><td><select name="tagsin" size="4">
			<?php foreach ($tags as $tag) echo '<option value="' . $tag->get_id() . '">' . $tag->get_name() . '</option>'; ?>
			</select> (only search for machines with these tags)</td></tr>
		<tr><td>Exclude Tags:</td><td><select name="tagsex" size="4">
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