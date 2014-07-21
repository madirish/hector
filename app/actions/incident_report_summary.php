<?php
/**
 * Incident report summary page
 * 
 * @package HECTOR
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 */
include_once($approot . 'templates/admin_headers.tpl.php');
include_once($approot . 'lib/class.Incident.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$report = new Incident($id);

include_once($approot . 'templates/incident_report_summary.tpl.php');
?>