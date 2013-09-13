<?php
/**
 * This is the default subcontroller for assets 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2011.02.22
 * @package HECTOR
 * @todo Move the SQL out of actions/summary.php and into a helper class
 */


// Queries (inefficiently done)
/**
 * Require the database
 */
require_once($approot . 'lib/class.Db.php');
$db = Db::get_instance();
global $appuser;
if (! isset($appuser)) {
	if (! isset($_SESSION['user_id'])) die("<h2>Fatal error!<?h2>User not initialized.");
	else $appuser = new User($_SESSION['user_id']);
} 

// Count of top 10 ports
$sql = 'SELECT DISTINCT(CONCAT(n.nmap_result_port_number, "/", n.nmap_result_protocol)) AS port_number, '  .
		'COUNT(n.nmap_result_id) AS portcount ' .
		'FROM nmap_result n ';
if ($appuser->get_is_admin()) {
	$sql .= 'WHERE n.state_id = 1 ' .
		'GROUP BY nmap_result_port_number ' .
		'ORDER BY portcount DESC ' .
		'LIMIT 10 ';
}
else {
	$sql .= ", host h, user_x_supportgroup x " .
			"WHERE n.host_id = h.host_id AND h.supportgroup_id = x.supportgroup_id " .
			"AND x.user_id = " . $appuser->get_id() . " AND n.state_id = 1 " .
			"GROUP BY nmap_result_port_number " .
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
$sql = "SELECT CONCAT(dst_port, '/', proto) AS port, count(id) AS cnt " .
		"FROM darknet WHERE received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) " .
		"GROUP BY port ORDER BY cnt DESC LIMIT 10";
$probe_result = $db->fetch_object_array($sql);
$count = $hostcount[0]->hostcount;

$sql = 'SELECT COUNT(scan_type_id) AS thecount FROM scan_type';
$retval = $db->fetch_object_array($sql);
$scripts = $retval[0]->thecount;

$sql = 'SELECT COUNT(scan_id) AS thecount FROM scan';
$retval = $db->fetch_object_array($sql);
$scans = $retval[0]->thecount;

$nohosts = "No hosts tracked.  <a href='?action=config&object=add_hosts'>Add hosts</a>.";

$count = ($count == "0") ? $nohosts : number_format($count);
if ($count == 0) {
	$javascripts .= '<script type="text/javascript">$(document).ready( function(){jQuery.noConflict();$("#addHostsModal").modal("show");} )</script>' . "\n";
}
elseif ($scripts == 0) {
	$javascripts .= '<script type="text/javascript">$(document).ready( function(){jQuery.noConflict();$("#addScriptModal").modal("show");} )</script>' . "\n";
}
elseif ($scans == 0) {
	$javascripts .= '<script type="text/javascript">$(document).ready( function(){jQuery.noConflict();$("#addScanModal").modal("show");} )</script>' . "\n";
}
// Put jQuery after modal declarations or there is a conflict
$javascripts .= "<script type='text/javascript' src='js/jquery.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/Chart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/portSummaryChart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/darknetSummaryChart.js'></script>\n";

$portSummaryLabels = "";
$portSummaryCounts = "";
$darknetSummaryLabels = "";
$darknetSummaryCounts = "";
foreach ($port_result as $row) {
	$portSummaryLabels .= $row->port_number . ',';
	$portSummaryCounts .= $row->portcount . ',';
} 
foreach ($probe_result as $row) {
	$darknetSummaryLabels .= $row->port . ',';
	$darknetSummaryCounts .= $row->cnt . ',';
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'summary.tpl.php');

$db->close();
?>