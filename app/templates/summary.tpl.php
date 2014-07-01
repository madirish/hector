<p class="lead"><?php echo $count;?> Hosts Tracked</p>

<div id="addHostsModal" class="modal hide fade" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h3>No hosts tracked</h3>
</div>
<div class="modal-body">
<p>You currently have no hosts tracked in the database.</p>
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
<a href="?action=add_hosts" class="btn btn-primary">Go to add hosts</a>
</div>
</div>

<div id="addScriptModal" class="modal hide fade" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h3>No scripts</h3>
</div>
<div class="modal-body">
<p>You currently do not have any scanning scripts configured.  Scan scripts are required 
to perform automated scans of hosts.</p>
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
<a href="?action=config&object=scan_type" class="btn btn-primary">Go to script configuration</a>
</div>
</div>

<div id="addScanModal" class="modal hide fade" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h3>No scheduled scans</h3>
</div>
<div class="modal-body">
<p>You currently do not have any scripts scheduled or scans configured.</p>
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
<a href="?action=config&object=scan" class="btn btn-primary">Go to scan schedule</a>
</div>
</div>


<div class="row">
  <div class="span6">
  	<h3>Scanner:  Top Ports Detected</h3>
  	<div id="portSummaryChartLabels" class="hidden"><?php echo $portSummaryLabels;?></div>
  	<div id="portSummaryChartData" class="hidden"><?php echo $portSummaryCounts;?></div>
	<canvas id="topPortsChart" width="400"></canvas>
	
	
	<table class="table table-striped table-condensed" id="top-ports-dectected">
	<thead>
	<tr><th>#</th><th>Port Number</th><th>Protocol</th><th>Hosts with port open</th></tr>
	</thead>
	<tbody>
	<?php
	$x=1;
	foreach ($port_result as $row) {
		echo "<tr";
		if ($x%2) echo " class='odd'";
        $portproto = explode('/', $row->port_number);
		echo "><td>" . $x++ . "</td><td><a href='?action=reports&report=by_port&ports=" .
				$row->port_number . "'>" . $row->port_number . "</a></td><td>" . 
                getservbyport($portproto[0],$portproto[1]) . "</td><td>" . $row->portcount . "</td></tr>";
	}
	?>
	</tbody>
	</table>
	<div id="incident-div">
		<div id="incidentReportHeader" class="hidden"><?php echo $incident_report_header?></div>
		<div id="incidentChartLabels" class="hidden"><?php echo $incidentchart_labels?></div>
		<div id="incidentChartCounts" class="hidden"><?php echo $incidentchart_counts?></div>
		<h3 id="incidentChartHeader"></h3>
		<canvas id="incidentChart"></canvas>
		<div id="incidentChartLegend"></div>
	</div>
  </div>
  
  <div class="span6">
	<h3>Darknet:  Top Port Probes in Last 4 Days</h3>
	<canvas id="darknetChart"  width="400"></canvas>
	

	<div id="darknetSummaryChartLabels" class="hidden"><?php echo $darknetSummaryLabels;?></div>
  	<div id="darknetSummaryChartData" class="hidden"><?php echo $darknetSummaryCounts;?></div>
	<table class="table table-striped table-condensed" id="darknet-probes-summary">
	<thead>
	<tr><th>#</th><th>Port Number</th><th>Protocol</th><th>Total darknet probes</th></tr>
	</thead>
	<tbody>
	<?php
	$x=1;
	foreach ($probe_result as $row) {
        $portproto = explode('/', $row->port);
		echo "<tr";
		if ($x%2) echo " class='odd'";
		echo "><td>" . $x++ . "</td><td><a href='?action=reports&report=by_port&ports=" .$row->port ."'> " .
				$row->port . "</a></td><td>" . getservbyport($portproto[0],$portproto[1]) . "</td><td>" . $row->cnt . "</td></tr>";
	}
	?>
	</tbody>
	</table>

	</div>
</div>

