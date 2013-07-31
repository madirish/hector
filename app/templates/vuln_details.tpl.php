<span id="editspan">
<a class="btn btn-primary" title="Edit this item" href="?action=edit_vuln_details&id=<?php echo $vuln_details->get_id();?>">Edit</a>
</span><h1>Vunerability Details</h1>
<table class="table" id="vuln_details">
	<tbody>
	<tr><td>text</td><td><?php echo $vuln_details->get_text();?></td></tr>
	<tr><td>Discovered</td><td><?php echo $vuln_details->get_datetime();?></td></tr>
	<tr><td>host</td><td><a href=?action=details&object=host&id=<?php echo $vuln_details->get_host_id();?>><?php echo $vuln_details->get_host_name();?></a></td></tr>
	<tr><td>ignore</td><td><?php if ($vuln_details->get_ignore() == 1) echo '<i class="icon-ok"></i>';?></td></tr>
	<tr><td>fixed</td><td><?php if ($vuln_details->get_fixed() == 1) echo '<i class="icon-ok"></i>';?></td></tr>
	<tr><td>fixed time</td><td><?php echo $vuln_details->get_fixed_datetime();?></td></tr>
	<tr><td>fixed notes</td><td><?php echo $vuln_details->get_fixed_notes();?></td></tr>
	</tbody>
	</table>