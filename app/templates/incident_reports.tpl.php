<h2>Incident Reports</h2>
<div class="row-fluid">
    <div class="span3">
        <div class="well">
        <h4>Top Threat Agent</h4>
        <p><?php echo $agentpercent . "% ". $agent_names[0]; ?></p>
        </div>
    </div>
    <div class="span3">
        <div class="well">
        <h4>Top Threat Actions</h4>
        <p><?php echo $actionpercent . "% ". $action_names[0]; ?></p>
        </div>
    </div>
    <div class="span3">
        <div class="well">
        <h4>Top Assets Affected</h4>
        <p><?php echo $assetpercent . "% ". $asset_names[0]; ?></p>
        </div>
    </div>
    <div class="span3">
        <div class="well">
        <h4>Top Discovery Method</h4>
        <p><?php echo $discopercent . "% ". $disco_names[0]; ?></p>
        </div>
    </div>
</div>

<div class="row-fluid">
<div class="span12 pagination-centered">
<h3>Timeline of Incident Reports</h3>
        <canvas id="incidentCountChart" height="300" width="600"></canvas>
        <script>
        $(document).ready(function(){
            var data = {labels: [<?php foreach ($months as $year=>$month){ foreach (array_keys($month) as $monthnum) { echo '"' . $monthnames[$monthnum] . ' ' . $year . '",' ;}}?>],
                        datasets: [
                            {
                                label: "My First dataset",
                                fillColor: "rgba(255,255,255,1)",
                                strokeColor: "rgba(220,220,220,1)",
                                pointColor: "rgba(220,220,220,1)",
                                pointStrokeColor: "#fff",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(220,220,220,1)",
                                data: [<?php foreach ($months as $year) {foreach ($year as $month) echo $month . ',';} ?>]
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