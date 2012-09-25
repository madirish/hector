<script type="text/javascript">
$(document).ready(function() 
    { 
        $("#darknet-probes-summary").tablesorter(); 
        $("#top-ports-detected").tablesorter(); 
    } 
); 
</script>

<p class="lead"><?php echo $count;?> Hosts Tracked</p>


<div class="row">
  <div class="span6">

	<h3>Scanner:  Top Ports Detected</h3>
	<table class="table table-striped" id="top-ports-dectected">
	<thead>
	<tr><th>#</th><th>Port Number</th><th>Hosts with port open</th></tr>
	</thead>
	<tbody>
	<?php
	$x=1;
	foreach ($port_result as $row) {
		echo "<tr";
		if ($x%2) echo " class='odd'";
		echo "><td>" . $x++ . "</td><td><a href='?action=reports&report=by_port&ports=" .
				$row->port_number . "'>" . $row->port_number . "</a></td><td>" . $row->portcount . "</td></tr>";
	}
	?>
	</tbody>
	</table>

	</div><div class="span6">
	<h3>Darknet:  Top Port Probes in Last 4 Days</h3>
	<table class="table table-striped table-condensed" id="darknet-probes-summary">
	<thead>
	<tr><th>#</th><th>Port Number</th><th>Total darknet probes</th></tr>
	</thead>
	<tbody>
	<?php
	$x=1;
	foreach ($probe_result as $row) {
		echo "<tr";
		if ($x%2) echo " class='odd'";
		echo "><td>" . $x++ . "</td><td><a href='?action=reports&report=by_port&ports=" .$row->port ."'> " .
				$row->port . "</a></td><td>" . $row->cnt . "</td></tr>";
	}
	?>
	</tbody>
	</table>

	</div>
</div>

