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
include_once($approot . 'lib/class.Vuln_detail.php');
include_once($approot . 'lib/class.Vuln.php');
include_once($approot . 'lib/class.Risk.php');
include_once($approot . 'actions/functions.php');
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
hector_add_js('portSummaryChart.js');
hector_add_js('darknetSummaryChart.js');


hector_add_js('summaryCharts.js');
hector_add_js('legend.js');

// jQuery jvectormap
hector_add_js('jquery-jvectormap-1.2.2.min.js');
hector_add_js('jquery-jvectormap-1.2.2-map.js');
hector_add_css('jquery-jvectormap-1.2.2.css');
hector_add_css('jquery-ui-1.8.22.custom.css');


// Include kojoneymap Script
hector_add_js('kojoneymap.js');

// Include Tag cloud Scripts
hector_add_js('jquery.tagcloud.js');
hector_add_js('tagcloud.js');

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
 * Vulnerability Pie Charts
 */

// Breakdown of Vuln_detail by Risk
$risk_nums = array();
$risk_coll = new Collection('Risk', ' AND t.risk_name != "none" ');
if (is_array($risk_coll->members)) {
	foreach ($risk_coll->members as $risk) {
		$risk_nums[$risk->get_name()]['count'] = count($risk->get_most_recent_vuln_detail_ids());
		$risk_nums[$risk->get_name()]['href'] = '?action=risk_rating&risk_id=' . $risk->get_id();
	}
}
$vuln_num_report_header = 'Vulnerability Risk Counts';
$vuln_num_chart_labels = json_encode(array_keys($risk_nums));
$vuln_num_chart_counts = json_encode($risk_nums);

/**
 * Incidents Pie Chart
 */

$incident_reports = new Collection('Incident','','get_incidents_in_last_year');
$action_count = array();
$asset_count = array();
$sorter = array();
$asset_count_sorter = array();


if (is_array($incident_reports->members)) {
    foreach ($incident_reports->members as $irreport){
    	// Get incident action count
    	$action = $irreport->get_action()->get_action();
        $action_count[$action]['href'] = '?action=incident_reports&threat_action=' . $irreport->get_action_id();
    	if (isset($action_count[$action]['count'])){
    		$action_count[$action]['count'] += 1;
    	}
        else{
    		$action_count[$action]['count'] = 1;
    	}
    	$sorter[$action] = $action_count[$action]['count'];
    	// Get Asset count
    	$asset = $irreport->get_asset()->get_name();
    	$asset_count[$asset]['href'] = "?action=incident_reports&asset_id=".$irreport->get_asset_id();
    	if (isset($asset_count[$asset]['count'])){
    		$asset_count[$asset]['count'] += 1;
    	}else{
    		$asset_count[$asset]['count'] = 1;
    	}
    	$asset_count_sorter[$asset] = $asset_count[$asset]['count'];
    	
    }
    array_multisort($sorter,SORT_DESC,$action_count);
    array_multisort($asset_count_sorter,SORT_DESC,$asset_count);
}


$incidentchart_counts = json_encode($action_count);
$incidentchart_labels = json_encode(array_keys($action_count));
$asset_count_json = json_encode($asset_count);


$incident_report_header = "Incident Reports " . $timespan;
$asset_count_header = "Assets Affected $timespan";
$asset_labels_json = json_encode(array_keys($asset_count));

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

/**
 * Tag cloud
 */

$tag_collection = new Collection('Tag');
$tag_weights = array();
$tag_sorter = array();

if (is_array($tag_collection->members)){
	foreach($tag_collection->members as $tag){
		$name = $tag->get_name();
		$id = $tag->get_id();
		$incidents = count($tag->get_incident_ids());
		$vulns = count($tag->get_vuln_ids());
		$articles = count($tag->get_article_ids());
		$hosts = count($tag->get_host_ids());
		$weight = $incidents + $vulns + $articles + $hosts;
		// Don't include empty tags or unique tags to clean up display
		if ($weight > 1) {
			$tag_weights[] = array('name'=>$name,'id'=>$id,'weight'=>$weight);
			$tag_sorter[$name] = $weight;
		}
	}
	array_multisort($tag_sorter,SORT_DESC,$tag_weights);
	$tag_cloud = array_slice($tag_weights, 0, 50); // Limit to 50 elements
}

/**
 * Count summary
 */
$article_count = new Collection('Article','AND a.article_date > DATE_SUB(NOW(),INTERVAL 1 DAY)');
$article_count = isset($article_count->members) ? count($article_count->members) : 0;
$ossec_count = new Collection('Ossec_Alert', 'AND o.alert_date > DATE_SUB(NOW(),INTERVAL 1 DAY)');
$ossec_count = isset($ossec_count->members) ? count($ossec_count->members) : 0;
$honeypot_count = new Collection('HoneyPotConnect', 'AND k.time > DATE_SUB(NOW(),INTERVAL 1 DAY)');
$honeypot_count = isset($honeypot_count->members) ? count($honeypot_count->members) : 0;
$probe_count = new Collection('Darknet', 'AND d.received_at > DATE_SUB(NOW(),INTERVAL 1 DAY)');
$probe_count = isset($probe_count->members) ? count($probe_count->members) : 0;


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'summary.tpl.php');

?>