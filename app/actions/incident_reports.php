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
}
else {
	$incident_reports = new Collection('Incident');
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