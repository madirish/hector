<script src="js/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() 
    { 
        $("#darknet-probes-summary").tablesorter(); 
        $("#top-ports-detected").tablesorter(); 
    } 
); 
</script>
<script src="js/Chart.js" type="text/javascript"></script>
<p class="lead"><?php echo $count;?> Hosts Tracked</p>


<div class="row">
  <div class="span6">
  	<h3>Scanner:  Top Ports Detected</h3>
	<canvas id="topPortsChart" width="400"></canvas>
	<script>
	var data = {
	  labels : [<?php
	  	foreach ($port_result as $row) {
	  		print '"' . $row->port_number . '",';
	  	} 
	  ?>],
	  datasets : [ 
			{ 
				fillColor : "rgba(220,220,220,0.2)",
				strokeColor : "rgba(220,220,220,1)",
				data : [<?php
					foreach ($port_result as $row) {
				  		print $row->portcount . ',';
				  	}
				?>]
			}
		]
	}
	var myNewChart = new Chart(document.getElementById("topPortsChart").getContext("2d")).Bar(data);
	</script>
	
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
	<canvas id="darknetChart"  width="400"></canvas>
	<script>
	var data = {
	  labels : [<?php
	  	foreach ($probe_result as $row) {
	  		print '"' . $row->port . '",';
	  	} 
	  ?>],
	  datasets : [ 
			{ 
				fillColor : "rgba(220,220,220,0.2)",
				strokeColor : "rgba(220,220,220,1)",
				data : [<?php
					foreach ($probe_result as $row) {
				  		print $row->cnt . ',';
				  	}
				?>]
			}
		]
	}
	var myNewChart = new Chart(document.getElementById("darknetChart").getContext("2d")).Bar(data);
	</script>
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

