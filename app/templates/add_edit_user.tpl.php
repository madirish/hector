<form name="<?php echo $form_name;?>" id="<?php echo $form_name;?>" method="POST" action="?action=add_edit_scr&object=<?php echo $object;?>&id=<?php echo $id;?>">
<fieldset>
<legend><?php echo (isset($_GET['id'])) ? 'Edit' : 'Add';?> <?php echo $object;?></legend>
<table id="add-edit-table">
<?php
	foreach ($form_data as $row) {
		if ($row['label']=='User password' && isset($_SERVER['COSIGN_SERVICE'])) {
				// Hide the password row if using CoSign
				$row['form'] = str_ireplace('type="password"', 'type="hidden"', $row['form']);
				// Set a default when adding
				if (! isset($_GET['id'])) $row['form'] = str_ireplace('value=""', 'value="'.time().'"', $row['form']);
				echo $row['form'] . "\n";
		}
		else {
				echo "\t" . '<tr><td>' . $row['label'] . 
					'</td><td class="value">' . $row['form'] . '</td></tr>' . "\n";
		}
	}
?>	
<tr><td>&nbsp;</td>
<!--
<td><input type="button" name="submit" value="Save changes" onClick='javascript:if (checkAHForm()) {getPage("?action=add_edit_scr&object=<?php echo $object;?>&id=<?php echo $id;?>", "POST", "<?php echo $form_name;?>");}'/></td></tr>
-->
<td><input type="submit" name="submit" value="Save changes"/></td></tr>
</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>