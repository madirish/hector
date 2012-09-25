<p class="lead">Last 60 Alerts</p>
<table id="tablealerts" name="tablealerts" class="table table-striped">
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
			if (substr($alert->get_timestamp(), 0,10) != substr($prevstamp, 0, 10)) {
				echo '<tr><td colspan="3">&nbsp;</td></tr>';
				$prevstamp = $alert->get_timestamp();	
			}
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
<script type="text/javascript">
	$(document).ready(function() 
	    { 
	        $("#tablealerts").tablesorter(); 
	    } 
	); 
</script>