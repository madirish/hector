<script type="text/javascript">
$(document).ready(function() {
	$('#script').bind('change', function() {
		if ($('#script option:selected').attr('id') != '') {
			$('#configform').load('?action=ajax_load_scanfile&ajax=yes&scan=' + $('#script option:selected').attr('id') );
		}
	})
});
</script>

<form id="selector">
<fieldset>
<legend><?php echo (isset($_GET['id'])) ? 'Edit' : 'Add';?> script configution</legend>
<table id="add-edit-table">
<?php
	foreach ($form_data as $row) {
		if (strpos($row['form'], '<input type="hidden"') === 0) {
			// don't display these rows, they're there for callback processing only
		}
		else {
			echo "\t" . '<tr><td>' . $row['label'] . 
				'</td><td class="value">' . $row['form'] . 
				'</td></tr>' . "\n";
		}
	}
?>	
</table>
</fieldset>
</form>

<form name="add_scan_type_form" id="add_scan_type_form" method="POST" action="?action=add_edit_scr&object=<?php echo $object;?>&id=<?php echo $id;?>">
<div id="configform">

</div>
<input type="hidden" name="script" id="script"/>
<input type="hidden" id="flags" name="flags"/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>
<!-- JavaScripts have to go here so they can reach the parts of the DOM (above) they maniuplate -->
<?php 
global $javascripts;
if(isset($javascripts)  && is_array($javascripts)) foreach($javascripts as $script) echo $script;
?>