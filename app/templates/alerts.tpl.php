<p class="lead">Port Change Alerts</p>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.0/css/jquery.dataTables.css">
  
<!-- jQuery -->
<script type="text/javascript" charset="utf8" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
  
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>


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
	if (isset($alerts) && is_array($alerts)) {
		foreach ($alerts as $alert) {
			echo '<tr><td>' . $alert->get_timestamp() . 
				'</td><td>' . $alert->get_string();
			echo '</td><td>';
			echo $alert->get_host_linked(); 
			echo '</td></tr>'. "\n";
		}
	}
?>
</tbody>
</table>


<script type="text/javascript" >
$(document).ready( function () {
    $('#tablealerts').DataTable();
} );
</script>