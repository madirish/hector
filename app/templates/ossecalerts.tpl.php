<h2>OSSEC Alerts</h2>
<table class="table table-striped table-bordered">
<thead>
	<tr>
		<th>Date</th>
		<th>Host</th>
		<th>Source</th>
		<th>Rule</th>
		<th>Level</th>
		<th>IP</th>
		<th>Alert Message</th>
	</tr>
</thead>
<tbody>
<?php
	if (isset($alerts) && is_array($alerts)) {
		foreach ($alerts as $alert) {
			echo '<tr><td>' . $alert->alert_date . '</td>'; 
			echo '<td>' . $alert->host_id . '</td>'; 
			echo '<td>' . $alert->alert_log . '</td>'; 
			echo '<td>' . $alert->rule_message . '</td>'; 
			echo '<td>' . $alert->rule_level . '</td>'; 
			echo '<td>' . $alert->alert_src_ip . '</td>'; 
			echo '<td>' . $alert->rule_log . '</td></tr>'; 
			echo "\n";
		}
	}
?>
</tbody>
</table>