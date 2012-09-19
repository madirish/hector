<div id="summary">
<p>
<h2>Hosts Tracked</h2>: <?php echo $count;?>
</p>
<table>
<tr>
<td id="common-ports-detected">

	<h2>Scanner:  Top Ports Detected</h2>
	<table class="summary">
	<tr><th>#</th><th>Port Number</th><th>Hosts with port open</th></tr>
	<?php
	$x=1;
	foreach ($port_result as $row) {
		echo "<tr";
		if ($x%2) echo " class='odd'";
		echo "><td>" . $x++ . "</td><td><a href='?action=reports&report=by_port&ports=" .
				$row->port_number . "'>" . $row->port_number . "</a></td><td>" . $row->portcount . "</td></tr>";
	}
	?>
	</table>
</td><td id="darknet-probes">
	<h2>Darknet:  Top Port Probes in Last 4 Days</h2>
	<table class="summary">
	<tr><th>#</th><th>Port Number</th><th>Total darknet probes</th></tr>
	<?php
	$x=1;
	foreach ($probe_result as $row) {
		echo "<tr";
		if ($x%2) echo " class='odd'";
		echo "><td>" . $x++ . "</td><td><a href='?action=reports&report=by_port&ports=" .$row->port ."'> " .
				$row->port . "</a></td><td>" . $row->cnt . "</td></tr>";
	}
	?>
	</table>
</td>
</tr>
</table>



</div>