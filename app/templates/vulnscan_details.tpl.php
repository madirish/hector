<h3>Vulnerability Scan Details</h3>
<h2><?php echo $vulnscan->get_name();?></h2>
<h2><?php echo $vulnscan->get_datetime();?></h2>
<h3>Critical:<?php echo $vulnscan->get_risk_count('critical')?></h3>
<h3>High:<?php echo $vulnscan->get_risk_count('high')?></h3>
<h3>Medium:<?php echo $vulnscan->get_risk_count('medium')?></h3>
<h3>Low:<?php echo $vulnscan->get_risk_count('low')?></h3>
<?php 
$runs = $vulnscan->get_previous_runs();
foreach ($runs as $run) {
	print $run->get_datetime() . " has " . count($run->get_vuln_detail_ids()) . " vulns<br/>";
}
$current_scan = $vulnscan;
foreach ($runs as $run) {
	print "<hr/>Comparing " . count($current_scan->get_vuln_detail_ids()) . " to " . count($run->get_vuln_detail_ids()) . "<br/>";
	$delta = $current_scan->delta($run);
	print"<p>Diffing " . $run->get_datetime() . " with " . $current_scan->get_datetime() . "</p>"; 
	$newly_detected = $delta[0];
	$fixed = $delta[1];
	print "On " . $current_scan->get_datetime() . " there were:<br/>";
	print "<b>" . count($newly_detected->get_vuln_detail_ids()) . " new vulnerabilities</b><br/>";
	foreach ($newly_detected->get_vuln_details() as $detail) {
		$vuln = new Vuln($detail->get_vuln_id());
		print "<a href='?action=host_details&id=" . $detail->get_host_id() . "'>" . $vuln->get_name() . "</a> " . $detail->get_host_id() . "<br/>";
	}
	print "<b>" . count($fixed->get_vuln_detail_ids()) . " vulnerabilities fixed (no longer detected)</b><br/>";
	foreach ($fixed->get_vuln_details() as $detail) {
		$vuln = new Vuln($detail->get_vuln_id());
		print "<a href='action=host_details&id=" . $detail->get_host_id() . "'>" . $vuln->get_name() . "</a> " . $detail->get_host_id() . "<br/>";
	}
	print "<p>";
	$current_scan = $run;
}
print "The first scan was on " . $current_scan->get_datetime() . " with " . count($current_scan->get_vuln_detail_ids()) . " vulnerabilities";
?>


<canvas id="myChart" width="400" height="400"></canvas>
<script>
$(document).ready(function(){
	var ctx = document.getElementById("myChart");
	var myChart = new Chart(ctx, {
	    type: 'bar',
	    data: {
	        labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
	        datasets: [{
	            label: '# of Votes',
	            data: [12, 19, 3, 5, 2, 3]
	        }]
	    },
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
