<?php
/** 
 * This is the subcontroller for the incident report summary page
 * 
 * @package HECTOR
 */
 
/**
 * Require the collection class
 */
include_once($approot . 'lib/class.Collection.php');

if (isset($_GET['threat_action'])) {
    $incident_reports = new Collection('Incident', intval($_GET['threat_action']), 'get_incidents_by_action');	
}elseif (isset($_GET['asset_id'])){
	$incident_reports = new Collection('Incident', intval($_GET['asset_id']),'get_incidents_by_asset');
}
else {
    $agents = array();
    $actions = array();
    $assets = array();
    $discos = array();
	$incident_reports = new Collection('Incident');
    if (is_array($incident_reports->members)) {
    	foreach($incident_reports->members as $report) {
            $agent_name = $report->get_agent_name();
    		if (isset($agents[$agent_name])) {
    			$agents[$agent_name] ++;
    		}
            else {
            	$agents[$agent_name] = 1;
            }
            
            $action_name = $report->get_action_name();
            if (isset($actions[$action_name])) {
                $actions[$action_name] ++;
            }
            else {
                $actions[$action_name] = 1;
            }
            
            $assets_name = $report->get_asset_name();
            if (isset($assets[$assets_name])) {
                $assets[$assets_name] ++;
            }
            else {
                $assets[$assets_name] = 1;
            }
            
            $disco_name = $report->get_discovery_method_friendly();
            if (isset($discos[$disco_name])) {
                $discos[$disco_name] ++;
            }
            else {
                $discos[$disco_name] = 1;
            }
    	}
    }
    arsort($agents); 
    $agent_names = array_keys($agents);
    $agent_values = array_values($agents);
    $totalagent = array_sum($agents);
    $topagent = $agent_values[0];
    $agentpercent = round(($topagent / $totalagent) * 100);
    
    arsort($actions); 
    $action_names = array_keys($actions);
    $action_values = array_values($actions);
    $actiontotal = array_sum($actions);
    $topaction = $action_values[0];
    $actionpercent = round(($topaction / $actiontotal) * 100);
    
    arsort($assets); 
    $asset_names = array_keys($assets);
    $asset_values = array_values($assets);
    $assettotal = array_sum($assets);
    $topasset = $asset_values[0];
    $assetpercent = round(($topasset / $assettotal) * 100);
    
    arsort($discos); 
    $disco_names = array_keys($discos);
    $disco_values = array_values($discos);
    $discototal = array_sum($discos);
    $topdisco = $disco_values[0];
    $discopercent = round(($topdisco / $discototal) * 100);
    
}

if (isset($incident_reports->members)) {
    foreach ($incident_reports->members as $report) {
    	$incidents[] = $report;
    }	
}

$chartlabels = array();
$chartvalues = array();
$incidentYearMonthCount = array();
foreach ($incidents as $incident) {
	$iyear = $incident->get_year();
	$imonth = $incident->get_month();
	if (! isset($incidentYearMonthCount[$iyear][$imonth])) {
		$incidentYearMonthCount[$iyear][$imonth] = 1;
	}
	else {
		$incidentYearMonthCount[$iyear][$imonth] ++;
	}
}
ksort($incidentYearMonthCount);
$firstyear = TRUE;
foreach(array_keys($incidentYearMonthCount) as $year) {
	ksort($incidentYearMonthCount[$year]);
	$startval = 1;
	if ($firstyear) { 
		$firstkey = array_keys($incidentYearMonthCount[$year]);
		$startval = $firstkey[0];
	}
	for ($x=$startval; $x<=12; $x++) {
		if (! isset($incidentYearMonthCount[$year][$x])) $incidentYearMonthCount[$year][$x] = 0;
		if ($year == date('Y') && $x == date('n')) {
			break;
		}
	}
	if ($firstyear) $firstyear = FALSE;
	ksort($incidentYearMonthCount[$year]);
}
// Our month array is 1-12 so we just create an empty element 0
$monthnames = array("[huh?]","January","February","March","April","May","June","July","August","September","October","November","December");

foreach($incidentYearMonthCount as $year=>$values) {
	foreach ($incidentYearMonthCount[$year] as $month=>$val) {
		$chartlabels[] = '"' . $monthnames[$month] . " " . $year . '"';
		$chartvalues[] = $val;
	}
}

$javascripts .= "<script type='text/javascript' src='js/incident_reports.js'></script>\n";

include_once($approot . 'templates/admin_headers.tpl.php');
include_once($approot . 'templates/incident_reports.tpl.php');
?> 