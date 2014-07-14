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
 * Includes
 */
require_once($approot . 'lib/class.Db.php');
include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.Report.php');
global $appuser;
if (! isset($appuser)) {
	if (! isset($_SESSION['user_id'])) die("<h2>Fatal error!<?h2>User not initialized.");
	else $appuser = new User($_SESSION['user_id']);
} 

$report = new Report();
$port_result = $report->topTenPorts($appuser);
$count = $report->getHostCount($appuser);
$probe_result = $report->darknetSummary();
$scripts = $report->scriptCount();
$scans = $report->scanCount();

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

// jQuery jvectormap
$javascripts .= "<link href='css/jquery-jvectormap-1.2.2.css' rel='stylesheet'>\n";
$javascripts .= "<link href='css/jquery-ui-1.8.22.custom.css' rel='stylesheet'>\n";
$javascripts .= "<script type='text/javascript' src='js/jquery-jvectormap-1.2.2.min.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/jquery-jvectormap-1.2.2-map.js'></script>\n";

//Include incidentChart script
$javascripts .= "<script type='text/javascript' src='js/incidentChart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/legend.js'></script>\n";

// Include kojoneymap Script
$javascripts .= "<script type='text/javascript' src='js/kojoneymap.js'></script>\n";

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

$month = date('F');
$cy = date('Y');
$ly = $cy - 1;
$timespan =    $month . ' ' . $ly . ' - ' . $cy ;


/**
 * Incidents Pie Chart
 */

$incident_reports = new Collection('Incident','','get_incidents_in_last_year');
$action_count = array();
$sorter = array();

if (is_array($incident_reports->members)) {
    foreach ($incident_reports->members as $irreport){
    	$action = $irreport->get_action()->get_action();
        $action_count[$action]['href'] = '?action=incident_summaries&threat_action=' . $irreport->get_action_id();
    	if (isset($action_count[$action]['count'])){
    		$action_count[$action]['count'] += 1;
    	}
        else{
    		$action_count[$action]['count'] = 1;
    	}
    	$sorter[$action] = $action_count[$action]['count'];
    }
    array_multisort($sorter,SORT_DESC,$action_count);
}


$incidentchart_counts = json_encode($action_count);
$IRAction_labels = array_keys($action_count);
$incidentchart_labels = json_encode($IRAction_labels);

$incident_report_header = json_encode("Incident Reports " . $timespan);

/**
 * Darknet map
 */

$darknetmapcounts = $report->getDarknetCountryCount();

/**
 * Darknet Country Trends
 */
$datelabels = array();
for ($i=6; $i>=0; $i--) {
	$datelabels[$i] = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-$i,date('Y')));
}
$topCountries = $report->getTopDarknetCountries();
$countrycountdates = array();
foreach ($topCountries as $country) {
	foreach($datelabels as $datelabel) {
		$countrycountdates[$country][$datelabel] = $report->getProbesByCountryDate($country, $datelabel);
	}
}

/**
 * Kojoney login attempt map
 */
$kojoneyCountryCount = $report->getKojoneyCountryCount();
$kojoneymapcounts = json_encode($kojoneyCountryCount);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'summary.tpl.php');

$db->close();
?>