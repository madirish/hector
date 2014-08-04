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
    asort($agents);
    $agent_names = array_keys($agents);
    $agent_values = array_values($agents);
    $totalagent = array_sum($agents);
    $topagent = $agent_values[0];
    $agentpercent = ($topagent / $totalagent) * 100;
    
    asort($actions);
    $action_names = array_keys($actions);
    $action_values = array_values($actions);
    $actiontotal = array_sum($actions);
    $topaction = $action_values[0];
    $actionpercent = ($topaction / $actiontotal) * 100;
    
    asort($assets);
    $asset_names = array_keys($assets);
    $asset_values = array_values($assets);
    $assettotal = array_sum($assets);
    $topasset = $asset_values[0];
    $assetpercent = ($topasset / $assettotal) * 100;
    
    asort($discos);
    $disco_names = array_keys($discos);
    $disco_values = array_values($discos);
    $discototal = array_sum($discos);
    $topdisco = $disco_values[0];
    $discopercent = ($topdisco / $discototal) * 100;
    
}

$incidents = array();
if (isset($incident_reports->members)) {
    foreach ($incident_reports->members as $report) {
    	$incidents[] = $report;
    }	
}

$javascripts .= '<script type="text/javascript" charset="utf8" src="js/jquery.dataTables.js"></script>' . "\n";
$javascripts .= '<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">' . "\n";

include_once($approot . 'templates/admin_headers.tpl.php');
include_once($approot . 'templates/incident_reports.tpl.php');
?> 