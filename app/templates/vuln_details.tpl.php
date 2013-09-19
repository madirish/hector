<span id="editspan">
<a class="btn btn-primary" title="Edit this item" href="?action=edit_vuln_details&id=<?php echo $vuln_details->get_id();?>">Edit</a>
</span><h3>Vunerability Details</h3>
<table class="table" id="vuln_details">
	<tbody>
	<tr>
		<td>Type</td>
		<td><?php echo $vuln_details->get_vuln()->get_name();?></td>
	</tr><tr>
		<td>Description</td>
		<td><?php echo $vuln_details->get_vuln()->get_description();?></td>
	</tr><tr>
		<td>CVE</td>
		<td><?php echo $vuln_details->get_vuln()->get_cve();?></td>
	</tr><tr>
		<td>OSVDB</td>
		<td><?php echo $vuln_details->get_vuln()->get_osvdb();?></td>
	</tr><tr>
		<td>Tags</td>
		<td></td>
	</tr><tr>
		<td>Text</td>
		<td><?php echo $vuln_details->get_text();?></td>
	</tr><tr>
		<td>Host</td>
		<td><a href=?action=host_details&id=<?php echo $vuln_details->get_host_id();?>><?php echo $vuln_details->get_host_name();?></a></td>
	</tr><tr>
		<td>Discovered</td>
		<td><?php echo $vuln_details->get_datetime();?></td>
	</tr><tr>
		<td>Ignore</td>
		<td><?php if ($vuln_details->get_ignore() == 1) echo '<i class="icon-ok"></i>';?></td>
	</tr><tr>
		<td>Fixed</td>
		<td><?php if ($vuln_details->get_fixed() == 1) echo '<i class="icon-ok"></i>';?></td>
	</tr><tr>
		<td>Fixed Time</td>
		<td><?php echo $vuln_details->get_fixed_datetime();?></td>
	</tr><tr>
		<td>Fixed Notes</td>
		<td><?php echo $vuln_details->get_fixed_notes();?></td>
	</tr>
	</tbody>
</table>