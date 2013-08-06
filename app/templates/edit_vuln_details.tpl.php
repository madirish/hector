<h3>Edit Vunerability Details</h3>
<form name="<?php echo $form_name;?>" id="<?php echo $form_name;?>" method="POST" action="?action=edit_vuln_details&id=<?php echo $vuln_details->get_id();?>">
<fieldset>
<table class="table" id="table_edit_vuln_details">
	<tbody>
	<tr><td>Type</td><td><?php echo $vuln_details->get_vuln_name();?></td></tr>
	<tr><td>Description</td><td><?php echo $vuln_details->get_vuln_description();?></td></tr>
	<tr><td>CVE</td><td><?php echo $vuln_details->get_vuln_cve();?></td></tr>
	<tr><td>OSVDB</td><td><?php echo $vuln_details->get_vuln_osvdb();?></td></tr>
	<tr><td>Tags</td><td><?php echo 'Need to add tags to vuln_details class!!!';?></td></tr
	<tr><td>Text</td><td><textarea name ="text"><?php echo $vuln_details->get_text();?></textarea></td></tr>
	<tr><td>Host</td><td><a href=?action=details&object=host&id=<?php echo $vuln_details->get_host_id();?>><?php echo $vuln_details->get_host_name();?></a></td></tr>
	<tr><td>Discovered</td><td><?php echo $vuln_details->get_datetime();?></td></tr>
	<tr><td>Ignore</td><td><input type="checkbox" name="ignore" <?php echo ($vuln_details->get_ignore() == 1) ? 'checked="true"' : '' ;?>/></td></tr>
	<tr><td>Fixed</td><td><input type="checkbox" name="fixed" <?php echo ($vuln_details->get_fixed() == 1) ? 'checked="true"' : '' ;?>/></td></tr>
	<tr><td>Fixed Time</td><td><?php echo $vuln_details->get_fixed_datetime();?></td></tr>
	<tr><td>Fixed Notes</td><td><textarea name="fixed_notes"><?php echo $vuln_details->get_fixed_notes();?></textarea></td></tr>
	<tr><td>&nbsp;</td>
	<td><input type="submit" name="submit" value="Save changes"/></td></tr>
	</tbody>
</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>