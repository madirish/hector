<form name="<?php echo $form_name;?>" id="<?php echo $form_name;?>" method="POST" action="?action=add_edit_scr&object=<?php echo $object;?>&id=<?php echo $id;?>">
<fieldset>
<h3><?php echo (isset($_GET['id'])) ? 'Edit' : 'Add';?> <?php echo $object_readable;?></h3>
<table id="add-edit-table" class="table table-bordered">
<?php
	foreach ($form_data as $row) { 
		if (stripos($row['form'], 'type="hidden"') === FALSE) {
			echo "\t" . '<tr><td>' . $row['label'] . 
			'</td><td class="value">' . $row['form'] . '</td></tr>' . "\n";
		}
		else {
			echo "\t" . $row['form'] . "\n";
		}
	}
?>	
<tr><td>&nbsp;</td>
<!--
<td><input type="button" name="submit" value="Save changes" onClick='javascript:if (checkAHForm()) {getPage("?action=add_edit_scr&object=<?php echo $object;?>&id=<?php echo $id;?>", "POST", "<?php echo $form_name;?>");}'/></td></tr>
-->
<td><input type="submit" name="submit" value="Save changes" class="btn"/></td></tr>
</table>
</fieldset>
<div id="availableTags" class="hidden"><?php echo $tags_json?></div>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>

