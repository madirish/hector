<h3>Vunerability Details</h3>
<h4><a href="?action=host_details&id=<?php echo $vuln_detail->get_host_id();?>"><?php echo $vuln_detail->get_host_name();?></a></h4>
<table class="table" id="vuln_details">
	<tbody>
	<tr>
		<td class="span2">Type</td>
		<td class="span10"><?php echo $vuln_detail->get_vuln()->get_name();?></td>
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
		<td><?php echo $vuln_detail->get_text();?></td>
	</tr><tr>
		<td>Host</td>
		<td><a href="?action=host_details&id=<?php echo $vuln_detail->get_host_id();?>"><?php echo $vuln_detail->get_host_name();?></a></td>
	</tr><tr>
		<td>Discovered</td>
		<td><?php echo $vuln_detail->get_datetime();?></td>
	</tr><tr>
		<td>Ignore</td>
		<td><?php if ($vuln_detail->get_ignore() == 1) echo '<i class="icon-ok"></i>';?></td>
	</tr><tr>
		<td>Ignored by</td>
		<td><?php echo $vuln_detail->get_ignored_user_name();?></td>
	</tr><tr>
		<td>Fixed</td>
		<td><?php if ($vuln_detail->get_fixed() == 1) echo '<i class="icon-ok"></i>';?></td>
	</tr><tr>
		<td>Fixed by</td>
		<td><?php echo $vuln_detail->get_fixed_user_name();?></td>
	</tr><tr>
		<td>Fixed Time</td>
		<td><?php echo $vuln_detail->get_fixed_datetime();?></td>
	</tr><tr>
		<td>Fixed Notes</td>
		<td><?php echo $vuln_detail->get_fixed_notes();?></td>
	</tr>
	</tbody>
</table>
<span id="editspan">
<a class="btn btn-primary" title="Edit this item" href="?action=edit_vuln_details&id=<?php echo $vuln_detail->get_id();?>">Edit</a>
</span>
<script type="text/javascript">
if ($("#risk").html() == 'Risk: High') {
    $("#risk").addClass('btn btn-small btn-danger');
}
if ($("#risk").html() == 'Risk: Medium') {
	$("#risk").addClass('btn btn-small btn-warning');
}
if ($("#risk").html() == 'Risk: Low') {
    $("#risk").addClass('btn btn-small btn-info');
}
// Replace line breaks with br tags in plugin-output
var prettyString = $("#plugin-output").html().replace(/\n/g,"<br/>");
$("#plugin-output").html(prettyString);
</script>
