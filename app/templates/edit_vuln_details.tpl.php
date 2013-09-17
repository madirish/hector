<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/bootstrap-datepicker.js" type="text/javascript"></script>
<link href="css/datepicker.css" rel="stylesheet">
<h3>Edit Vunerability Details</h3>
<form name="<?php echo $form_name;?>" id="<?php echo $form_name;?>" method="POST" action="?action=edit_vuln_details&id=<?php echo $vuln_detail->get_id();?>">
<fieldset>
<table class="table" id="table_edit_vuln_details">
	<tbody>
	<tr>
		<td>Type</td>
		<td><?php echo $vuln_detail->get_vuln()->get_name();?></td>
	</tr><tr>
		<td>Description</td>
		<td><?php echo $vuln_detail->get_vuln()->get_description();?></td>
	</tr><tr>
		<td>CVE</td>
		<td><?php echo $vuln_detail->get_vuln()->get_cve();?></td>
	</tr><tr>
		<td>OSVDB</td>
		<td><?php echo $vuln_detail->get_vuln()->get_osvdb();?></td>
	</tr><tr>
		<td>Tags</td>
		<td></td>
	</tr><tr>
		<td>Text</td>
		<td><textarea name ="text"><?php echo $vuln_detail->get_text();?></textarea></td>
	</tr><tr>
		<td>Host</td>
		<td><a href=?action=host_details&id=<?php echo $vuln_detail->get_host_id();?>><?php echo $vuln_detail->get_host_id();?></a></td>
	</tr><tr>
		<td>Discovered</td>
		<td><?php echo $vuln_detail->get_datetime();?></td>
	</tr><tr>
		<td>Last seen</td>
		<td><?php echo $vuln_detail->get_datetime();?></td>
	</tr><tr>
		<td>Ignore</td>
		<td><input type="checkbox" name="ignore" <?php echo ($vuln_detail->get_ignore() == 1) ? 'checked="true"' : '' ;?>/></td>
	</tr><tr>
		<td>Fixed</td>
		<td><input type="checkbox" name="fixed" onclick="cbChanged(this);" <?php echo ($vuln_detail->get_fixed() == 1) ? 'checked="true"' : '' ;?>/></td>
	</tr><tr>
		<td>Fixed Time</td>
		<td><div class="input-append date" id="dp" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd">
    	<input name='fixed_date' data-format="yyyy-MM-dd hh:mm:ss" id='fixed_date' type="text" value="<?php echo $vuln_detail->get_fixed_datetime();?>"/>
    	<span class="add-on"><i class="icon-calendar"></i></span>
    	</div></td>
    </tr><tr>
		<td>Fixed Notes</td>
		<td><textarea name="fixed_notes"/><?php echo $vuln_detail->get_fixed_notes();?></textarea></td>
	</tr><tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Save changes"/></td>
	</tr>
	</tbody>
</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>
<script>
	var ddatetime = new Date(<?php echo strtotime($vuln_detail->get_datetime());?>*1000);
	var defaulttime = '<?php echo date(' H:i:s');?>';
    $('#dp').datepicker().on('changeDate', function(ev){
    	if (ev.date.valueOf() > ddatetime) {
				document.getElementById('fixed_date').value += defaulttime;
    	}
    	else {
    		document.getElementById('fixed_date').value='0000-00-00 00:00:00';
    		alert('Invalid Fixed Time');
    	}
    });

	function cbChanged(checkboxElem) {
  		if (checkboxElem.checked) {
			document.getElementById('fixed_date').value='<?php echo date('Y-m-d H:i:s');?>';
  		} else {
   			document.getElementById('fixed_date').value='0000-00-00 00:00:00';
  		}
	}
</script>