<h3>Vulnerability Report</h3>
<table class="table table-striped" id="vulns">
	<thead>
	<tr><th>Type</th><th>Host</th><th>Last seen</th><th>Fixed</th><th>Ignore</th></tr>
	</thead>
    <tbody>
	<?php foreach ($vuln_details as $vuln_detail): ?>
		<tr>
            <td><a href="?action=vuln_details&id=<?php echo $vuln_detail->get_id();?>"><?php echo $vuln_detail->get_vuln_name();?></a></td>
            <td><a href="?action=host_details&id=<?php echo $vuln_detail->get_host_id();?>"><?php echo $vuln_detail->get_host_name();?></a></td>
            <td><?php echo $vuln_detail->get_datetime();?></td>
            <td><?php echo ($vuln_detail->get_fixed()==1 ? '<i class="icon-ok"></i>':'') ;?></td>
            <td><?php echo ($vuln_detail->get_ignore()==1 ? '<i class="icon-ok"></i>':'') ;?></td>
        </tr>
	<?php endforeach;?>
	</tbody>
</table>