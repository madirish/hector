<p class="lead">Port Change Alerts</p>

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
    var table = $('#tablealerts').DataTable({
    	"ordering": true
    });
    table.column('0:visible').order('desc');
    table.draw();
} );
</script>