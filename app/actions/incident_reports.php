<?php
include_once($approot . 'templates/admin_headers.tpl.php');
include_once($approot . 'lib/class.Collection.php');

$incident_reports = new Collection('Incident');
$incidents = array();
foreach ($incident_reports->members as $report) {
	$incidents[] = $report;
}

include_once($approot . 'templates/incident_reports.tpl.php');
?>