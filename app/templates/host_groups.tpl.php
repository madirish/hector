
<?php
if (isset($_GET['host_group_id'])) {
    echo "<h3>" . $hostgroup->get_name() . " Hostgroup</h3>\n<ul>";
	foreach ($hosts as $host) {
		echo "<li><a href='?action=host_details&id=" . 
        $host->get_id() . "'>" . $host->get_ip() . "</a></li>\n";
	}
}
else {
    echo "<h3>Host Groups</h3>\n<ul>";
    foreach ($hostgroups as $hostgroup) {
        echo "<li>" . 
        $hostgroup->get_name() . "</a> " .
        "<a href='?action=host_groups&host_group_id=" . 
        $hostgroup->get_id() . "'>" . count($hostgroup->get_host_ids()) . 
        " hosts</a></li>\n";
    }
}
?>
</ul>
