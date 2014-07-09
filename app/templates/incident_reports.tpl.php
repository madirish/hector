<h2>Incident Reports</h2>
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
    table.column('0:visible').order('desc');
    table.draw();
} );
</script>
<?php echo $deletemodaldivs;?>