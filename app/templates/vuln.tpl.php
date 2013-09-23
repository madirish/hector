<h3>Vulnerability Report</h3>
<table class="table table-striped" id="vulns">
	<tbody>
	<thead>
	<tr><th>Type</th><th>Host</th><th>Last seen</th><th>Fixed</th><th>Ignore</th></tr>
	</thead>
	<?php foreach ($vuln_details->members as $vuln_detail) {
		$output = '<tr><td><a href=?action=vuln_details&id=' . $vuln_detail->get_id() . '>' . $vuln_detail->get_vuln_name() . '</a></td>';
		$output .= '<td><a href=?action=host_details&id=' . $vuln_detail->get_host_id() . '>' . $vuln_detail->get_host_name(). '</a></td>';
		$output .= '<td>' . $vuln_detail->get_datetime() . '</td>';
		$output .= '<td>' . ($vuln_detail->get_fixed()==1 ? '<i class="icon-ok"></i>':'') . '</td>';
		$output .= '<td>' . ($vuln_detail->get_ignore()==1 ? '<i class="icon-ok"></i>':'') . '</td></tr>';
		echo $output;
		}?>
	</tbody>
</table>