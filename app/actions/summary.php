<?php
/**
 * This is the default subcontroller for assets 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2011.02.22
 */


// Queries (inefficiently done)

require_once($approot . 'lib/class.Db.php');
$db = Db::get_instance();
global $appuser;
if (! isset($appuser)) {
	if (! isset($_SESSION['user_id'])) die("<h2>Fatal error!<?h2>User not initialized.");
	else $appuser = new User($_SESSION['user_id']);
} 

// Count of top 10 ports
$sql = "select distinct(n.nmap_scan_result_port_number) as port_number, " .
		"count(n.nmap_scan_result_id) as portcount from nmap_scan_result n ";
if ($appuser->get_is_admin()) {
	$sql .= "where n.state_id = 1 " .
		"group by nmap_scan_result_port_number " .
		"order by portcount desc " .
		"limit 10 ";
}
else {
	$sql .= ", host h, user_x_supportgroup x " .
			"WHERE n.host_id = h.host_id AND h.supportgroup_id = x.supportgroup_id " .
			"AND x.user_id = " . $appuser->get_id() . " AND n.state_id = 1 " .
			"GROUP BY nmap_scan_result_port_number " .
			"ORDER BY portcount desc " .
			"LIMIT 10 ";
}

$port_result = $db->fetch_object_array($sql);

if ($appuser->get_is_admin())
	$sql = "select count(host_id) as hostcount from host";
else {
	$sql = "SELECT COUNT(h.host_id) AS hostcount FROM host h, " .
			"user_x_supportgroup x " .
			"WHERE h.supportgroup_id = x.supportgroup_id" .
			" AND x.user_id = " . $appuser->get_id();
}
$hostcount = $db->fetch_object_array($sql);

// Darknet summary:
$sql = "SELECT DISTINCT(dst_port) AS port, count(id) AS cnt " .
		"FROM darknet WHERE received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) " .
		"GROUP BY port ORDER BY cnt DESC LIMIT 20";
$probe_result = $db->fetch_object_array($sql);

$count = $hostcount[0]->hostcount;
$nohosts = "No hosts tracked.  <a href='?action=config&object=add_hosts'>Add hosts</a>.";
$count = ($count == "0") ? $nohosts : number_format($count);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'summary.tpl.php');

$db->close();
?>