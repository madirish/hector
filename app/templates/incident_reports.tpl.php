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
        	<div class="panel-footer"><h4><?php echo $agent_names[0]; ?></h4></div>
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
        	<div class="panel-footer"><h4><?php echo $action_names[0]; ?></h4></div>
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
        	<div class="panel-footer"><h4><?php echo $asset_names[0]; ?></h4></div>
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
        	<div class="panel-footer"><h4><?php echo $disco_names[0]; ?></h4></div>
        	<div class="hidden" id="discopercent"><?php echo $discopercent;?></div>
        </div>
    </div>
</div>

<div class="row-fluid">
<div class="span12 pagination-centered">
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Timeline of Incident Reports</h3>
		
	</div>
	<div class="panel-body">
		<canvas id="incidentCountChart" height="300" width="600"></canvas>
	</div>
</div>
<script>
        $(document).ready(function(){
            var data = {labels: [<?php echo join(',', $chartlabels);?>],
                        datasets: [
                            {
                                label: "My First dataset",
                                fillColor: "rgba(255,255,255,0.1)",
                                strokeColor: "#05EDFF",
                                pointColor: "#05EDFF",
                                pointStrokeColor: "#fff",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(220,220,220,1)",
                                data: [<?php echo join(',', $chartvalues); ?>]
                            }
                        ]
            };
            var options = {
                bezierCurve: false,
                multiTooltipTemplate: "<%= datasetLabel%> - <%= value %>",
            };
            var myNewChart = new Chart(document.getElementById("incidentCountChart").getContext("2d")).Line(data, options);
            $("#incidentCountChart").hover(function (evt) {
                var activeBars = myNewChart.getPointsAtEvent(evt);
                console.log(activeBars);
            });        
        });
</script>
</div>
</div>

<table id="incidenttable" name="incidenttable" class="table table-striped table-bordered">
<thead>
<tr>
    <th>Number</th>
    <th>Year - Month</th>
    <th>Title</th>
    <th>Agent</th>
    <th>Threat action</th>
    <th>Asset affected</th>
    <th>Overall impact</th>
    <th>&nbsp;</th>
</tr>
</thead>
<tbody>

<?php 
$i = 1;
$deletemodaldivs = '';
foreach ($incidents as $incident) {
	echo "\t<tr><td>";
    echo $i;
    $i++;
    echo "</td><td>";
    echo $incident->get_year();
    echo " - ";
    echo $incident->get_month_friendly() . "</td>";
    echo '<td><a href="?action=incident_report_summary&id=' . $incident->get_id() . '">';
    echo $incident->get_title();
    echo "</a></td>";
    echo '<td>' . $incident->get_agent()->get_name() . '</td>';
    echo '<td>' . $incident->get_action()->get_action() . '</td>';
    echo '<td>' . $incident->get_asset()->get_name() . '</td>';
    echo '<td>' . $incident->get_impact_magnitude_friendly() . '</td>';
    echo "<td><a href='#deleteModal" . $incident->get_id() . "' role='button' class='btn' data-toggle='modal'>Delete</a></td></tr>\n";
    $deletemodaldivs .= "<div id='deleteModal" . $incident->get_id() . "' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='deletemodal" . $incident->get_id() ."' aria-hidden='true'>";
    $deletemodaldivs .= "<div class='modal-header'><h3>Are you sure?</h3></div>";
    $deletemodaldivs .= "<div class='modal-body'><p>Are you sure you want to <em>permanently</em> delete this report?</p></div>";
    $deletemodaldivs .= "<div class='modal-footer'><button class='btn' data-dismiss='modal' aria-hidden='true'>No, return to view</button>";
    $deletemodaldivs .= "<a href='?action=incident_report_delete&id=" . $incident->get_id() . "' class='btn btn-primary'>Yes, delete!</a></div>";
    $deletemodaldivs .= "</div>\n";
}
?>
</tbody>
</table>
<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#incidenttable').DataTable({
        "ordering": true,
        aoColumnDefs: [{
            bSortable: false,
            aTargets: [ -1 ]
        }]
    });
    table.column('0:visible').order('asc');
    table.draw();
} );
</script>
<?php echo $deletemodaldivs;?>