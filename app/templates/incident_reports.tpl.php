<h2>Incident Reports</h2>
<table class="table table-bordered table-striped">
<?php 
$i = 1;
foreach ($incidents as $incident) {
	echo "\t<tr><td>";
    echo $i;
    $i++;
    echo "</td><td>(";
    echo $incident->get_year();
    echo " - ";
    echo $incident->get_month_friendly();
    echo ') <a href="?action=incident_report_summary&id=' . $incident->get_id() . '">';
    echo $incident->get_title();
    echo "</a></td>";
    echo "<td><a href='#deleteModal" . $incident->get_id() . "' role='button' class='btn' data-toggle='modal'>Delete</a></td></tr>\n";
    echo "<div id='deleteModal" . $incident->get_id() . "' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='deletemodal" . $incident->get_id() ."' aria-hidden='true'>";
    echo "<div class='modal-header'><h3>Are you sure?</h3></div>";
    echo "<div class='modal-body'><p>Are you sure you want to <em>permanently</em> delete this report?</p></div>";
    echo "<div class='modal-footer'><button class='btn' data-dismiss='modal' aria-hidden='true'>No, return to view</button>";
    echo "<a href='?action=incident_report_delete&id=" . $incident->get_id() . "' class='btn btn-primary'>Yes, delete!</a></div>";
    echo "</div>\n";
}
?>
</table>