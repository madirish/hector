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

/**
 * Include the Collection class
 */
include_once($approot . 'lib/class.Collection.php');

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
$count = $hostcount[0]->hostcount;

// Darknet summary:
$sql = "SELECT CONCAT(dst_port, '/', proto) AS port, count(id) AS cnt " .
		"FROM darknet WHERE received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) " .
		"GROUP BY port ORDER BY cnt DESC LIMIT 10";
$probe_result = $db->fetch_object_array($sql);

$sql = 'SELECT COUNT(scan_type_id) AS thecount FROM scan_type';
$retval = $db->fetch_object_array($sql);
$scripts = $retval[0]->thecount;

$sql = 'SELECT COUNT(scan_id) AS thecount FROM scan';
$retval = $db->fetch_object_array($sql);
$scans = $retval[0]->thecount;

$nohosts = "No hosts tracked.  <a href='?action=config&object=add_hosts'>Add hosts</a>.";

$count = ($count == "0") ? $nohosts : number_format($count);
if ($count == 0 && $appuser->get_is_admin()) {
	$javascripts .= '<script type="text/javascript">$(document).ready( function(){jQuery.noConflict();$("#addHostsModal").modal("show");} )</script>' . "\n";
}
elseif ($scripts == 0 && $appuser->get_is_admin()) {
	$javascripts .= '<script type="text/javascript">$(document).ready( function(){jQuery.noConflict();$("#addScriptModal").modal("show");} )</script>' . "\n";
}
elseif ($scans == 0 && $appuser->get_is_admin()) {
	$javascripts .= '<script type="text/javascript">$(document).ready( function(){jQuery.noConflict();$("#addScanModal").modal("show");} )</script>' . "\n";
}
// Put jQuery after modal declarations or there is a conflict
$javascripts .= "<script type='text/javascript' src='js/jquery.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/Chart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/portSummaryChart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/darknetSummaryChart.js'></script>\n";

//Include incidentChart script
$javascripts .= "<script type='text/javascript' src='js/incidentChart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/legend.js'></script>\n";

$portSummaryLabels = "";
$portSummaryCounts = "";
$darknetSummaryLabels = "";
$darknetSummaryCounts = "";

/**
 * Chart.js requires count strings to be the same length, so 
 * 8,7,4,3 will work, but 10,8,4,3 will not, instead we need
 * 10,08,04,03 for some reason.
 */
$maxlen = 0;
if (count($port_result) > 0) {
	$maxlen = strlen(strval($port_result[0]->portcount)); // Max string length for Chart.js bug
}
foreach ($port_result as $row) {
	while (strlen(strval($row->portcount)) < $maxlen) {
		$row->portcount = '0' . $row->portcount;
	}
	$portSummaryLabels .= $row->port_number . ',';
	$portSummaryCounts .= $row->portcount . ',';
}
$portSummaryLabels = trim($portSummaryLabels, ',');
$portSummaryCounts = trim($portSummaryCounts, ',');

$maxlen = 0;
if (count($probe_result) > 0) {
	$maxlen = strlen(strval($probe_result[0]->cnt)); // Max string length for Chart.js bug
}
foreach ($probe_result as $row) {
	while (strlen(strval($row->cnt)) < $maxlen) {
		$row->cnt = '0' . $row->cnt;
	}
	$darknetSummaryLabels .= $row->port . ',';
	$darknetSummaryCounts .= $row->cnt . ',';
}
$darknetSummaryLabels = trim($darknetSummaryLabels, ',');
$darknetSummaryCounts = trim($darknetSummaryCounts, ',');

// Incident Summary of Incident summary Chart


$IRActions = new Collection('IRAction');
$IRAction_labels = array();

foreach($IRActions->members as $action){
	$IRAction_labels[] = $action->get_action();
}

$incidentchart_labels = json_encode($IRAction_labels);

$incident_reports = new Collection('Incident');
$action_count = array();
foreach ($incident_reports->members as $report){
	$action = $report->get_action()->get_action();
	if (array_key_exists($action,$action_count)){
		$action_count[$action] += 1;
	}else{
		$action_count[$action] = 1;
	}
	
}

$incidentchart_counts = json_encode($action_count);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'summary.tpl.php');

$db->close();
?>