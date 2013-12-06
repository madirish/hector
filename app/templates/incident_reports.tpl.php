<ol>
<?php 
foreach ($incidents as $incident) {
	echo "\t<li>(";
    echo $incident->get_year();
    echo " - ";
    echo $incident->get_month_friendly();
    echo ') <a href="?action=incident_report_summary&id=' . $incident->get_id() . '">';
    echo $incident->get_title();
    echo "</a></li>\n";
}
?>
</ol>