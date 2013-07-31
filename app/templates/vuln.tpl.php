<h2>Vulnerability Report</h2>
<table class="table table-striped" id="vulns">
	<tbody>
	<thead>
	<tr><th>Vuln</th><th>host</th><th>date</th><th>fixed</th><th>ignore</th></tr>
	</thead>
	<?php foreach ($vulns as $vuln) {
		$output = '<tr><td><a href=?action=vuln_details&id=' . $vuln->vuln_details_id . '>' . $vuln->vuln_name . '</a></td>';
		$output .= '<td><a href=?action=details&object=host&id=' . $vuln->host_id . '>' . $vuln->host_name. '</a></td>';
		$output .= '<td>' . $vuln->vuln_details_datetime . '</td>';
		$output .= '<td>' . ($vuln->vuln_details_fixed==1 ? '<i class="icon-ok"></i>':'') . '</td>';
		$output .= '<td>' . ($vuln->vuln_details_ignore==1 ? '<i class="icon-ok"></i>':'') . '</td></tr>';
		echo $output;
		}?>
	</tbody>
	</table>