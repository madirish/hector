<h2>IP's <?php if(isset($range)) echo "in $range";?></h2>
<?php
$class = (isset($_GET['classB'])) ? 'classC' : 'classB';

if (isset($hosts) && is_array($hosts)) {
	print "<table class='table table-striped'><th>Hostname</th><th>IP</th><th>Open Ports</th><th>OS</th></tr>";
	foreach ($hosts as $host) {
		print "<tr><td><a href='?action=details&object=Host&id=" . $host->get_id();
		print "'>".$host->get_name()."</a></td><td>".$host->get_ip()."</td>";
		print "<td>" . $host->get_open_ports() . "</td>";
		print "<td>" . $host->get_os() . "</td>";
		print "</tr>";
	}
	print "</table>";
}
else {
	print "<ul class='nav nav-tabs nav-stacked'>";
	foreach($result as $row) {
		print "<li><a href='?action=browse_ip&$class=". $row->ipclass ."'>";
		print $row->ipclass . " <span class='badge badge-info'>" . $row->thecount . " hosts</span></a></li>";
	}
	print "</ul>";
}

?>