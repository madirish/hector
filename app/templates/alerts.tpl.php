<p class="lead">Port Change Alerts</p>
<!-- Sorter --> 
<form method="post" name="alert_filter_form" id="alert_filter_form" action="<?php echo $href;?>&start=<?php echo $startrecord;?>">
	<div class="input-prepend input-append">
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
<table id="tablealerts" name="tablealerts" class="table table-striped table-bordered">
<thead>
<tr>
	<th>Timestamp</th>
	<th>Message</th>
	<th>Asset</th>
</tr>
</thead>
<tbody>
<?php
$prevstamp = "";
	if (isset($alerts) && is_array($alerts)) {
		foreach ($alerts as $alert) {
			/*if (substr($alert->get_timestamp(), 0,10) != substr($prevstamp, 0, 10)) {
				echo '<tr><td colspan="3">&nbsp;</td></tr>';
				$prevstamp = $alert->get_timestamp();	
			}*/
			echo '<tr><td>' . $alert->get_timestamp() . 
				'</td><td>' . $alert->get_string();
			echo '</td><td>';
			if ($alert->get_host_linked() != '<a href="?action=details&object=host&id=0"></a>')
				echo $alert->get_host_linked(); 
			echo '</td></tr>'. "\n";
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