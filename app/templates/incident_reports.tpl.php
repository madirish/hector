<script type="text/javascript">
// Global variables for jQuery document ready functions
//var readyCharts = new Array();
var readyDatas = new Array();
//var dataSets = new Array();
//var data = new Array();

var colors = ['#eee', '#475053', '#2e94b9', '#acdcee', '#f0fbff'];
//ar barChartLabels = new Array();
//var magnitudeLabels = new Array();


// Build up the datasets
function buildDatasets(inObj, mLables, chartLabels, searchItem) {
	var dataSets = new Array();
	for (var i=0; i<mLables.length; i++) {
		var dataGroup = [];
		dataGroup['label'] = mLables[i];
		dataGroup['backgroundColor'] = colors[i];
		dataGroup['borderColor'] = '#666';
		dataGroup['borderWidth'] = 1;
		var datas = [];
		for (var x=0; x<chartLabels.length; x++) {
			var found = 0;
			for (y=0; y<inObj.length; y++) {
				if (inObj[y][searchItem] == chartLabels[x] && inObj[y]['magnitude_name'] == mLables[i]) {
					datas.push(inObj[y]['magnitudeCounts']);
					found = 1;
				}
			}
			if (found == 0) { datas.push(0); }
		}
		dataGroup['data'] = datas;
		dataSets.push(dataGroup);
	}
	return dataSets;
}

function parsePushObject(parseObject, labelString, hrefText='', hrefId='') {
    // Parse up JSON object and prepare Chart.js data for display in ready funtion 
    var magnitudeLabels = new Array();
	var dataSets = new Array();
	var barChartLabels = new Array();
	var summaryLinks = 'Detailed report for: ';

	// Build up the lables
	for (var i=0; i<parseObject.length; i++) {
		if (barChartLabels.indexOf(parseObject[i][labelString]) < 0) {
		    barChartLabels.push(parseObject[i][labelString]);
		    var link = "<a href='?action=incident_reports&" + hrefText + "=";
		    link += parseObject[i][hrefId];
		    link += "'>" + parseObject[i][labelString] + "</a>, ";
		    summaryLinks += link;
		}
		if (magnitudeLabels.indexOf(parseObject[i]['magnitude_name']) < 0) {
		    magnitudeLabels.push(parseObject[i]['magnitude_name']);
		}
	}

	dataSets = buildDatasets(parseObject, magnitudeLabels, barChartLabels, labelString);

	if (hrefText == '') { summaryLinks = ''; }
	
	var data = {
		labels: barChartLabels,
		datasets: dataSets,
		links: summaryLinks,
	};
	return data;
}

</script>

<?php if (isset($threat_action)) {?>
<h2>Incidents with Threat Action: <?php echo $threat_action->get_action(); ?></h2>
<?php } else if (isset($asset)) { ?>
<h2>Incidents Involving Asset: <?php echo $asset->get_name(); ?></h2>
<?php } else { ?>
<h2>Incident Reports</h2>
<div class="row-fluid">
    <div class="span3 pagination-centered">
        <div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Threat Agent</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="threat-agent"></canvas>		
        	</div>
        	<div class="panel-footer"><h4 id="topThreatAgent"><?php echo $agent_names[0] . " " . $agentpercent; ?>%</h4></div>
        	<div class="hidden" id="agentpercent"><?php echo $agentpercent;?></div>
        </div>
    </div>
    <div class="span3 pagination-centered">
    	<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Threat Actions</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="threat-action"></canvas>		
        	</div>
        	<div class="panel-footer"><h4 id="topThreatAction"><?php echo $action_names[0] . " " . $actionpercent; ?>%</h4></div>
        	<div class="hidden" id="actionpercent"><?php echo $actionpercent;?></div>
        </div>
    </div>  
    <div class="span3 pagination-centered">
    	<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Assets Affected</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="threat-asset"></canvas>		
        	</div>
        	<div class="panel-footer"><h4 id="topAssetAffected"><?php echo $asset_names[0] . " " . $assetpercent; ?>%</h4></div>
        	<div class="hidden" id="assetpercent"><?php echo $assetpercent;?></div>
        </div>
    </div>
    <div class="span3 pagination-centered">
    	<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Discovery Method</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="disco-method"></canvas>		
        	</div>
        	<div class="panel-footer"><h4 id="topDiscoveryMethod"><?php echo $disco_names[0] . " " . $discopercent; ?>%</h4></div>
        	<div class="hidden" id="discopercent"><?php echo $discopercent;?></div>
        </div>
    </div>
</div>

<!--  Magnitude breakdowns for agents and actions -->
<div class="row-fluid">
	<div class="panel panel-default span6">
		<div class="panel-heading">Incidents by Threat Agent and Magnitude</div>
		<div class="panel-body">
		<script type="text/javascript">
			var amobj = <?php echo json_encode($agent_magnitude);?>;
			readyDatas['agents'] = parsePushObject(amobj, 'agent_agent');
			console.info(readyDatas['agents']);
		</script>
		<canvas id="stackedAgentMagnitude"></canvas>
		</div>
		<div class="panel-footer" id="agentMagnitudeFooter">&nbsp;
		</div>
	</div>
	<div class="panel panel-default span6">
		<div class="panel-heading">Incidents by Threat Action and Magnitude</div>
		<div class="panel-body">
		<script type="text/javascript">

		var actionmobj = <?php echo json_encode($action_magnitude);?>;
		readyDatas['actions'] = parsePushObject(actionmobj, 'action_action', 'threat_action', 'action_id');
		console.info(readyDatas['actions']);
		</script>
		<canvas id="stackedActionMagnitude"></canvas>
		</div>
		<div class="panel-footer" id="threatActionFooter">&nbsp;</div>
	</div>
</div>


<!--  Magnitude breakdowns for assets and discovery methods -->
<div class="row-fluid">
	<div class="panel panel-default span12">
		<div class="panel-heading">Incidents by Assets Affected and Magnitude</div>
		<div class="panel-body">
		<script type="text/javascript">
			var assetObj = <?php echo json_encode($asset_magnitude);?>;
			readyDatas['assets'] = parsePushObject(assetObj, 'asset_asset', 'asset_id', 'asset_id');
			console.info(readyDatas['assets']);
		</script>
		<canvas id="stackedAssetMagnitude"></canvas>
		</div>
		<div class="panel-footer" id="assetMagnitudeFooter">&nbsp;</div>
	</div>
</div><div class="row-fluid">
	<div class="panel panel-default span12">
		<div class="panel-heading">Incidents by Discovery Method and Magnitude</div>
		<div class="panel-body">
		<script type="text/javascript">

		var discoveryObj = <?php echo json_encode($discovery_magnitude);?>;
		readyDatas['discoveries'] = parsePushObject(discoveryObj, 'discovery_method');
		console.info(readyDatas['discoveries']);
		</script>
		<canvas id="stackedDiscoveryMagnitude"></canvas>
		</div>
		<div class="panel-footer" id="discoveryFooter">&nbsp;</div>
	</div>
</div>



<?php } ?>

<div class="row-fluid">
<div class="span12 pagination-centered">
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Timeline of Incident Reports</h3>
		
	</div>
	<div class="panel-body">
		<canvas id="incidentCountChart" height="300" width="600"></canvas>
		<div class="hidden" id="incident-chart-labels"><?php echo json_encode($chartlabels);?></div>
		<div class="hidden" id="incident-chart-data"><?php echo json_encode($chartvalues);?></div>
	</div>
</div>

</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<table id="incidenttable" name="incidenttable" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Date</th>
				<th>Title</th>
				<th>Agent</th>
				<th>Threat action</th>
				<th>Asset affected</th>
				<th>Overall impact</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $incidents as $incident):?>
				<tr>
					<td><div class="hidden"><?php echo ($incident->get_year()*100) + $incident->get_month();?></div><?php echo $incident->get_year() . " - " . $incident->get_month_friendly();?></td>
					<td><a href="?action=incident_report_summary&id=<?php echo $incident->get_id()?>"><?php echo $incident->get_title();?></a></td>
					<td><?php echo $incident->get_agent()->get_name()?></td>
					<td><?php echo $incident->get_action()->get_action();?></td>
					<td><?php echo $incident->get_asset()->get_name();?></td>
					<td><?php echo $incident->get_impact_magnitude_friendly();?></td>
					<td><a href='<?php echo "#deleteModal" . $incident->get_id()?>' role='button' class='btn' data-toggle='modal'>Delete</a>
						<div id='<?php echo "deleteModal" . $incident->get_id();?>' role='button' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='<?php echo "deletemodal" . $incident->get_id();?>' aria-hidden='true'>
							<div class='modal-header'><h3>Are you sure?</h3></div>
							<div class='modal-body'><p>Are you sure you want to <em>permanently</em> delete this report?</p></div>
							<div class='modal-footer'>
								<button class='btn' data-dismiss='modal' aria-hidden='true'>No, return to view</button>
								<a href='?action=incident_report_delete&id=<?php echo $incident->get_id()?>' class='btn btn-primary'>Yes, delete!</a>
							</div>
						</div>
					</td>
				</tr>
				
			<?php endforeach;?>
		</tbody>
		</table>
	</div>
</div>

<?php if (! isset($threat_action) && ! isset($asset)) {?>
<script type="text/javascript">
var chartOptions = {
    	scales: {
            xAxes: [{
                stacked: true
            }],
            yAxes: [{
                stacked: true
            }]
    	}
	};
$(document).ready(function () {
	var agentMagnitudeChart = new Chart(document.getElementById("stackedAgentMagnitude").getContext("2d"), {
    	type: 'bar',
    	data: readyDatas['agents'],
    	options: chartOptions,
    });

	var actionMagnitudeChart = new Chart(document.getElementById("stackedActionMagnitude").getContext("2d"), {
    	type: 'bar',
    	data: readyDatas['actions'],
    	options: chartOptions,
    });

	var assetMagnitudeChart = new Chart(document.getElementById("stackedAssetMagnitude").getContext("2d"), {
    	type: 'bar',
    	data: readyDatas['assets'],
    	options: chartOptions,
    });

	var discoveryMagnitudeChart = new Chart(document.getElementById("stackedDiscoveryMagnitude").getContext("2d"), {
    	type: 'bar',
    	data: readyDatas['discoveries'],
    	options: chartOptions,
    });

    $('#threatActionFooter').html(readyDatas['actions']['links']);
    $('#assetMagnitudeFooter').html(readyDatas['assets']['links']);
    // $('#assetMagnitudeFooter').html(assetLinks);
});

</script>
<?php } ?>
