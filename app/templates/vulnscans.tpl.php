<h3>Vulnerability Scans Imported</h3>
<table class="table table-striped" id="vulns">
	<thead>
	<tr><th>Date</th><th>Scan ID</th></tr>
	</thead>
    <tbody>
	<?php foreach ($vulnscans as $scan): ?>
		<tr>
            <td><?php echo $scan['vuln_detail_datetime']?></td>
            <td><a href="?action=vulnscan_details&id=<?php echo $scan['vulnscan_id'];?>&datetime=<?php echo $scan['vuln_detail_datetime']?>"><?php echo $scan['vulnscan_id'];?></a></a></td>
        </tr>
	<?php endforeach;?>
	</tbody>
</table>