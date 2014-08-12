<h2>OSSEC Alerts</h2>
<?php print_r($ossec_rules)?>
<div class="input-prepend input-append">
	<span class="add-on">Minimum alert level:</span>
	<select name="minlevel" class="input-mini">
		<?php echo $leveloptions;?>
	</select>
	<span class="add-on">Start date:</span>
	<input type="text" placeholder="<?php echo $startdateplaceholder;?>" name="startdate" class="input-small">
	<span class="add-on">End date:</span>
	<input type="text" placeholder="<?php echo $enddate;?>" name="enddate" class="input-small">
	<span class="add-on">IP:</span>
	<input type="text" placeholder="<?php echo $ip;?>" name="ip" class="input-small">
	<button type="submit" class="btn">Apply filter</button>
	</div>
	<input type="hidden" name="token" value="<?php echo $filter_form_token;?>"/>
	<input type="hidden" name="form_name" value="ossec_filter_form"/>
	<a href="<?php echo $clearfilterurl;?>"><button type="button" class="btn">Clear filters</button></a>
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

<strong><?php echo $thecount;?> records.</strong> 

<!-- Sorter --> 
<form method="post" name="ossec_filter_form" id="ossec_filter_form" action="<?php echo $href;?>&start=<?php echo $startrecord;?>">
	<div class="input-prepend input-append">
	<span class="add-on">Minimum alert level:</span>
	<select name="minlevel" class="input-mini">
		<?php echo $leveloptions;?>
	</select>
	<span class="add-on">Start date:</span>
	<input type="text" placeholder="<?php echo $startdateplaceholder;?>" name="startdate" class="input-small">
	<span class="add-on">End date:</span>
	<input type="text" placeholder="<?php echo $enddate;?>" name="enddate" class="input-small">
	<span class="add-on">IP:</span>
	<input type="text" placeholder="<?php echo $ip;?>" name="ip" class="input-small">
	<button type="submit" class="btn">Apply filter</button>
	</div>
	<input type="hidden" name="token" value="<?php echo $filter_form_token;?>"/>
	<input type="hidden" name="form_name" value="ossec_filter_form"/>
	<a href="<?php echo $clearfilterurl;?>"><button type="button" class="btn">Clear filters</button></a>
</form>

<!-- Pager -->
<div class="pagination">
    <ul>
    <?php echo $pager;?>
    </ul>
</div>

<!-- Output -->
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
			echo '<td>' . $alert->rule_src_ip . '</td>'; 
			echo '<td>' . $alert->rule_log . '</td></tr>'; 
			echo "\n";
		}
	}
?>
</tbody>
</table>

<!-- Pager -->
<div class="pagination">
    <ul>
    <?php echo $pager;?>
    </ul>
</div>