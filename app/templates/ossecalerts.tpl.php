<h2>OSSEC Alerts</h2>
<strong><?php echo $record_count?> records in last week</strong>
<div class="row">
	<div class="span12 pagination-centered">
		<h3>Timeline of Alerts</h3>
		<div class="hidden" id="timeline-keys"><?php echo $timeline_keys;?></div>
		<div class="hidden" id="timeline-values"><?php echo $timeline_values;?></div>
		<canvas id="ossec-timeline" height="300" width="600"></canvas>
	</div>
</div>
<div class="input-prepend input-append">
	<span class="add-on">Minimum alert level:</span>
	<input class="input-mini" name="minlevel" id="minlevel" type="text" pattern="\d*"  placeholder="0">
	<span class="add-on">IP:</span>
	<input type="text" placeholder="<?php echo $ipholder;?>" name="ip" class="input-small" id="ip">
	<button type="button" class="btn" id="clearbtn">Clear filters</button>
	</div>
</div>
<div class="row">
<div class="span12">
<table id="ossec-alerts-table" class="table table-striped table-bordered">
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
		<?php foreach ($ossec_alerts as $alert):?>
			<tr>
				<td><?php echo $alert['alert_date']?></td>
				<td><?php echo $alert['host_id']?></td>
				<td><?php echo $alert['alert_log']?></td>
				<td><?php echo $alert['rule_message']?></td>
				<td><?php echo $alert['rule_level']?></td>
				<td><?php echo $alert['rule_src_ip']?></td>
				<td><?php echo $alert['rule_log']?></td>
			</tr>
		<?php endforeach;?>
	</tbody>
</table>
</div>
</div>