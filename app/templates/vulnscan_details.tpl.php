<script type="text/javascript" src="js/chart-v2.js"></script>


<?php 


function sort_by_risk($a, $b) {
	return $a->get_risk_id() < $b->get_risk_id();
}

$latest_risk_counts = array($vulnscan->get_risk_count('critical'),
							$vulnscan->get_risk_count('high'),
							$vulnscan->get_risk_count('medium'),
							$vulnscan->get_risk_count('low'),
								);
$alltimes = array_reverse($vulnscan->get_all_runtimes());
$labels = '';

foreach ($alltimes as $time) {
	$labels .= '"' . $time . '",';
}

$critical_totals = array($vulnscan->get_risk_count('critical'));
$high_totals = array($vulnscan->get_risk_count('high'));
$medium_totals = array($vulnscan->get_risk_count('medium'));
$low_totals = array($vulnscan->get_risk_count('low'));


$critical_new_counts = array();
$critical_fixed_counts = array();
$high_new_counts = array();
$high_fixed_counts = array();
$medium_new_counts = array();
$medium_fixed_counts = array();

$runs = $vulnscan->get_previous_runs();

?>
<h3>Vulnerability Scan Details</h3>
<ul class="nav nav-tabs" id="vulnScanTab">
	<li class="active"><a href="#overview" data-toggle="tab">Overview</a></li>
	<li><a href="#detail" data-toggle="tab">Detail</a></li>
	<li><a href="#breakdown" data-toggle="tab">Breakdown</a></li>
	<li><a href="#by_machine" data-toggle="tab">By Machine</a></li>
</ul>

<div class="tab-content">

<!--  Overview Tab -->
<div class="tab-pane active" id="overview">

<table class="table table-bordered">
<tr> 
	<td width="25%">Scan name/id:</td>
	<td><?php echo $vulnscan->get_name();?></td>
</tr>
<tr>
	<td>Latest scan:</td>
	<td><?php 
		$date = new DateTime($vulnscan->get_datetime());
		echo date_format($date, 'l M d, Y');
	?></td>
</tr>
<tr>
	<td>Total number of vulns</td>
	<td><?php echo count($vulnscan->get_vuln_detail_ids()); ?></td>
</tr>
<tr>
	<td>Latest vulnerability breakdowns:</td>
	<td><canvas id="latestVulnTotals"></canvas></td>
</tr>
<tr>
	<td>Vulnerability Totals Over Time</td>
	<td><canvas id="timelineCounts"></canvas></td>
</tr>
<tr>
	<td>New and fixed critical vulnerabilities</td>
	<td><canvas id="criticalFixedNew"></canvas></td>
</tr>
<tr>
	<td>New and fixed high vulnerabilities</td>
	<td><canvas id="highFixedNew"></canvas></td>
</tr>
<tr>
	<td>New and fixed medium vulnerabilities</td>
	<td><canvas id="mediumFixedNew"></canvas></td>
</tr>
<tr>
	<td>New and fixed low vulnerabilities</td>
	<td><canvas id="lowFixedNew"></canvas></td>
</tr>

<?php 
$vulnscan_for_details = clone $vulnscan;
$current_scan = $vulnscan;
foreach ($runs as $run) {
	$critical_totals[] = $run->get_risk_count('critical');
	$high_totals[] = $run->get_risk_count('high');
	$medium_totals[] = $run->get_risk_count('medium');
	$low_totals[] = $run->get_risk_count('low');
	
	$delta = $current_scan->delta($run);
	print"<tr><td>\nDeltas between " . $run->get_datetime() . " and " . $current_scan->get_datetime() . "</td><td>"; 
	$newly_detected = $delta[0];
	$fixed = $delta[1];
	print "On " . $current_scan->get_datetime() . " there were ";
	print "<b>" . count($newly_detected->get_vuln_detail_ids()) . " new vulnerabilities</b><br/>";
	
	print "\t\n<table>";
	$new_details = $newly_detected->get_vuln_details();
	usort($new_details, 'sort_by_risk');
	
	foreach ($new_details as $detail) {
		$risk = new Risk($detail->get_risk_id());
		if ($risk->get_name() == 'none') continue;
		
		$vuln = new Vuln($detail->get_vuln_id());
		print "\t\t<tr><td>";
		switch ($risk->get_name()) {
    			case 'critical':
    				print "<span class='label label-important'>Critical</span>";
    				break;
    			case 'high':
    				print "<span class='label label-warning'>High</span>";
    				break;
    			case 'medium':
    				print "<span class='label label-info'>Medium</span>";
    				break;
    			case 'low':
    				print "<span class='label'>Low</span>";
    				break;
		}
		print "</td><td>";
		$host = new Host($detail->get_host_id());
		print "<a href='?action=host_details&id=" . $detail->get_host_id() . "'>" . $vuln->get_name() . "</a></td><td>" . $host->get_ip() . "</td></tr>\n";
	}
	print "\t</table>\n";
	
	print "<b>" . count($fixed->get_vuln_detail_ids()) . " vulnerabilities fixed (no longer detected)</b><br/>";
	print "\n\t<table>";
	$fixed_details = $fixed->get_vuln_details();
	usort($fixed_details, 'sort_by_risk');
	foreach ($fixed_details as $detail) {
		$risk = new Risk($detail->get_risk_id());
		if ($risk->get_name() == 'none') continue;
		$vuln = new Vuln($detail->get_vuln_id());
		print "\t\t<tr><td>";
		switch ($risk->get_name()) {
    			case 'critical':
    				print "<span class='label label-important'>Critical</span>";
    				break;
    			case 'high':
    				print "<span class='label label-warning'>High</span>";
    				break;
    			case 'medium':
    				print "<span class='label label-info'>Medium</span>";
    				break;
    			case 'low':
    				print "<span class='label'>Low</span>";
    				break;
		}
		print "</td><td>";
		$host = new Host($detail->get_host_id());
		print "<a href='?action=host_details&id=" . $detail->get_host_id() . "'>" . $vuln->get_name() . "</a></td><td>" . $host->get_ip() . "</td></tr>\n";
	}
	print "\t</table>\n";
	print"</td></tr>\n";

	$critical_new_counts[] = $newly_detected->get_risk_count('critical');
	$critical_fixed_counts[] = $fixed->get_risk_count('critical');
	$high_new_counts[] = $newly_detected->get_risk_count('high');
	$high_fixed_counts[] = $fixed->get_risk_count('high');
	$medium_new_counts[] = $newly_detected->get_risk_count('medium');
	$medium_fixed_counts[] = $fixed->get_risk_count('medium');
	$low_new_counts[] = $newly_detected->get_risk_count('low');
	$low_fixed_counts[] = $fixed->get_risk_count('low');
	
	$current_scan = $run;
}

$critical_new_counts[] = '0';
$critical_fixed_counts[] = '0';
$high_new_counts[] = '0';
$high_fixed_counts[] = '0';
$medium_new_counts[] = '0';
$medium_fixed_counts[] = '0';
$low_new_counts[] = '0';
$low_fixed_counts[] = '0';

?>
</table>
</div>
<div class="tab-pane" id="detail">
<table class="table table-stiped table-bordered">
<tr>
	<th width="5%">#</th>
	<th width="20%">Host IP</th>
	<th width="10%">Risk</th>
	<th>Vulnerability</th>
</tr>
<?php 
$x = 1;
$prev_host_id = 0;
foreach ($vulnscan_for_details->get_vuln_details() as $vuln_detail) {
	$risk = new Risk($vuln_detail->get_risk_id());
	$local_host_id = $vuln_detail->get_host_id();
?>
<tr>
	<td><?php echo $x; ?></td>
	<?php if ($local_host_id != $prev_host_id) { ?>
	<td><a href="?action=host_details&id=<?php echo $local_host_id; ?>"><?php echo $vuln_detail->get_host_ip();?></a></td>
	<?php } else {?>
	<td>&nbsp;</td>
	<?php } ?>
	<td><?php 
		switch ($risk->get_name()) {
    			case 'critical':
    				print "<span class='label label-important'>Critical</span>";
    				break;
    			case 'high':
    				print "<span class='label label-warning'>High</span>";
    				break;
    			case 'medium':
    				print "<span class='label label-info'>Medium</span>";
    				break;
    			case 'low':
    				print "<span class='label'>Low</span>";
    				break;
		}
	?></td>
	<td><a href="?action=vuln_details&id=<?php echo $vuln_detail->get_id(); ?>"><?php echo $vuln_detail->get_vuln_name();?></a> <a href="#myModal" role="button" class="btn-small" data-toggle="modal">Details</a></td>
<?php 
$prev_host_id = $local_host_id;
$x++;
} ?>
</table>
</div>
<!--  End Overview Tab -->

<!--  Detail Tab -->
<div class="tab-pane" id="detail">
<table class="table table-stiped table-bordered">
<tr>
	<th width="5%">#</th>
	<th width="10%">Risk</th>
	<th width="20%">Host IP</th>
	<th>Vulnerability</th>
</tr>
<?php 
$x = 1;
foreach ($vulnscan_for_details->get_vuln_details() as $vuln_detail) {
	$risk = new Risk($vuln_detail->get_risk_id());
?>
<tr>
	<td><?php echo $x; ?></td>
	<td><?php 
		switch ($risk->get_name()) {
    			case 'critical':
    				print "<span class='label label-important'>Critical</span>";
    				break;
    			case 'high':
    				print "<span class='label label-warning'>High</span>";
    				break;
    			case 'medium':
    				print "<span class='label label-info'>Medium</span>";
    				break;
    			case 'low':
    				print "<span class='label'>Low</span>";
    				break;
		}
	?></td>
	<td><a href="?action=host_details&id=<?php echo $vuln_detail->get_host_id(); ?>"><?php echo $vuln_detail->get_host_ip();?></a></td>
	<td><a href="?action=vuln_details&id=<?php echo $vuln_detail->get_id(); ?>"><?php echo $vuln_detail->get_vuln_name();?></a></td>
<?php 
$x++;
} ?>
</table>
</div>
<!--  End Detail Tab -->

<!--  Breakdown Tab -->
<div class="tab-pane" id="breakdown">
<table class="table table-stiped table-bordered">
<tr>
	<th width="5%">#</th>
	<th width="10%">IP</th>
	<th width="20%">Hostname</th>
	<th>Critical</th>
	<th>High</th>
	<th>Medium</th>
	<th>Low</th>
	<th>None</th>
	<th>Contact</th>
	<th>Notes</th>
</tr>
<?php 
$x = 1;
foreach ($hosts as $record) {
	$host = $record['host'];
?>
<tr>
	<td><?php echo $x; ?></td>
	<td><a href="?action=host_details&id=<?php echo $host->get_id(); ?>"><?php echo $host->get_ip();?></a></td>
	<td><?php echo $host->get_name();?></td>
	<td><?php echo $record['urgents'];?></td>
	<td><?php echo $record['criticals'];?></td>
	<td><?php echo $record['seriouses'];?></td>
	<td><?php echo $record['mediums'];?></td>
	<td><?php echo $record['minimals'];?></td>
	<td><?php echo $host->get_sponsor();?></td>
	<td><?php echo $host->get_note();?></td>
<?php 
$x++;
} ?>
</table>
</div>
<!--  End Breakdown Tab -->


<!--  By Machine Tab -->
<div class="tab-pane" id="by_machine">
<table class="table table-stiped table-bordered">
<tr>
	<th width="5%">#</th>
	<th width="10%">IP</th>
	<th width="20%">Hostname</th>
	<th>Severity</th>
	<th>Notes</th>
	<th>Contact</th>
	<th>Vuln</th>
	<th width="25%">Details</th>
</tr>
<?php 
$x = 1;
foreach ($hosts as $record) {
	$host = $record['host'];
	foreach ($record['vulns'] as $vuln) {
?>
<tr>
	<td><?php echo $x; ?></td>
	<td><a href="?action=host_details&id=<?php echo $host->get_id(); ?>"><?php echo $host->get_ip();?></a></td>
	<td><?php echo $host->get_name();?></td>
	<td><?php echo $vuln->risk_name ;?></td>
	<td><?php echo $host->get_note() ;?></td>
	<td><?php echo $host->get_sponsor() ;?></td>
	<td><?php echo $vuln->vuln_name ;?></td>
	<td width="25%"><?php echo $vuln->vuln_description ;?></td>
<?php 
	$x++;
	}
} ?>
</table>
</div>
<!-- End By Machine Tab -->

</div>



<script type="text/javascript">
$(document).ready(function(){

	// Latest vuln totals
	var data = {
	    labels: [
	        "Critical",
	        "High",
	        "Medium",
	        "Low"
	    ],
	    datasets: [
	        {
	            data: [<?php echo $latest_risk_counts[0] . ',' . $latest_risk_counts[1] . ',' . $latest_risk_counts[2] . ',' . $latest_risk_counts[3]?>],
	            backgroundColor: [
	                "#FF0000",
	                "#FF9900",
	                "#FFFF00",
	                "#CCCCCC",
	            ]
	        }]
	};
	var options = null;

	var myPieChart = new Chart($("#latestVulnTotals"),{
	    type: 'pie',
	    data: data,
	    options: options
	});

	// Timeline of counts
	var data = {
	    labels: [<?php echo $labels; ?>],
	    datasets: [
	        {
	            label: "Critical",
	            backgroundColor: "rgba(255,0,0,0.2)",
	            borderColor: "rgba(255,0,0,1)",
	            borderWidth: 1,
	            hoverBackgroundColor: "rgba(255,0,0,0.4)",
	            hoverBorderColor: "rgba(255,0,0,1)",
	            data: [<?php echo implode(',',array_reverse($critical_totals)) ?> ],
	        },
	        {
	            label: "High",
	            backgroundColor: "rgba(255,165,0,0.2)",
	            borderColor: "rgba(255,165,0,1)",
	            borderWidth: 1,
	            hoverBackgroundColor: "rgba(255,165,0,0.4)",
	            hoverBorderColor: "rgba(255,165,0,1)",
	            data: [<?php echo implode(',',array_reverse($high_totals)) ?>],
	        },
	        {
	            label: "Medium",
	            backgroundColor: "rgba(255,255,0,0.2)",
	            borderColor: "rgba(255,255,0,1)",
	            borderWidth: 1,
	            hoverBackgroundColor: "rgba(255,255,0,0.4)",
	            hoverBorderColor: "rgba(255,255,0,1)",
	            data: [<?php echo implode(',',array_reverse($medium_totals)) ?>],
	        },
	        {
	            label: "Low",
	            backgroundColor: "rgba(0,90,255,0.2)",
	            borderColor: "rgba(0,90,255,1)",
	            borderWidth: 1,
	            hoverBackgroundColor: "rgba(0,90,255,0.4)",
	            hoverBorderColor: "rgba(0,90,255,1)",
	            data: [<?php echo implode(',',array_reverse($low_totals)) ?>],
	        },
	    ]
	};
	var options = { stacked: true };
	var myBarChart = new Chart($("#timelineCounts"), {
	    type: 'bar',
	    data: data,
	    options: options
	});
	
	
	// Critical Fixed vs New
	var ctx = $("#criticalFixedNew");
	var data = {
		    labels: [<?php echo $labels ?>],
		    datasets: [
		        {
		            label: "Critical - New",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(255,0,0,0.4)",
		            borderColor: "rgba(255,0,0,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(255,0,0,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(255,0,0,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($critical_new_counts)) ?>],
		        },

		        {
		            label: "Critical - Fixed",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(0,0,255,0.4)",
		            borderColor: "rgba(0,0,255,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(0,0,255,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(0,0,255,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($critical_fixed_counts)) ?>],
		        },
		    ]
		};
	var myChart = new Chart(ctx, {
	    type: 'line',
	    data: data,
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    }
	});

	// High fixed vs new
	var ctx = $("#highFixedNew");
	var data = {
		    labels: [<?php echo $labels ?>],
		    datasets: [
		        {
		            label: "High - New",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(255,0,0,0.4)",
		            borderColor: "rgba(255,0,0,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(255,0,0,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(255,0,0,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($high_new_counts)) ?>],
		        },

		        {
		            label: "High - Fixed",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(255,165,0,0.4)",
		            borderColor: "rgba(255,165,0,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(255,165,0,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(255,165,0,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($high_fixed_counts)) ?>],
		        },
		    ]
		};
	var myChart = new Chart(ctx, {
	    type: 'line',
	    data: data,
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    }
	});

	// Medium new vs fixed
	var ctx = $("#mediumFixedNew");
	var data = {
		    labels: [<?php echo $labels ?>],
		    datasets: [
		        {
		            label: "Medium - New",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(255,0,0,0.4)",
		            borderColor: "rgba(255,0,0,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(255,0,0,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(255,0,0,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($medium_new_counts)) ?>],
		        },

		        {
		            label: "Medium - Fixed",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(0,0,255,0.4)",
		            borderColor: "rgba(0,0,255,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(0,0,255,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(0,0,255,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($medium_fixed_counts)) ?>],
		        },
		    ]
		};
	var myChart = new Chart(ctx, {
	    type: 'line',
	    data: data,
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    }
	});

	// Low new vs fixed
	var ctx = $("#lowFixedNew");
	var data = {
		    labels: [<?php echo $labels ?>],
		    datasets: [
		        {
		            label: "Low - New",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(200,200,200,0.4)",
		            borderColor: "rgba(200,200,200,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(200,200,200,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(200,200,200,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($low_new_counts)) ?>],
		        },

		        {
		            label: "Low - Fixed",
		            fill: false,
		            lineTension: 0.1,
		            backgroundColor: "rgba(100,100,100,0.4)",
		            borderColor: "rgba(100,100,100,1)",
		            borderCapStyle: 'butt',
		            borderDash: [],
		            borderDashOffset: 0.0,
		            borderJoinStyle: 'miter',
		            pointBorderColor: "rgba(100,100,100,1)",
		            pointBackgroundColor: "#fff",
		            pointBorderWidth: 1,
		            pointHoverRadius: 5,
		            pointHoverBackgroundColor: "rgba(100,100,100,1)",
		            pointHoverBorderColor: "rgba(220,220,220,1)",
		            pointHoverBorderWidth: 2,
		            pointRadius: 1,
		            pointHitRadius: 10,
		            data: [<?php echo implode(',',array_reverse($low_fixed_counts)) ?>],
		        },
		    ]
		};
	var myChart = new Chart(ctx, {
	    type: 'line',
	    data: data,
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    }
	});
});
</script>
