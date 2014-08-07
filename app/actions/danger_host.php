<?php
/**
 * Report on dangerous hosts, that is, hosts with four or 
 * more common server ports open
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Remove SQL into helper objects
 */

/**
 * Setup defaults.
 */
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Report.php');


$javascripts .= '<script type="text/javascript" charset="utf8" src="js/jquery.dataTables.js"></script>' . "\n";
$javascripts .= '<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">' . "\n";

$report = new Report();
$host_results = $report->get_seven_port_hosts();
$sevenporthosts = array();
foreach ($host_results as $result) {
	$sevenporthosts[] = new Host($result->host_id);
}

$host_results = $report->get_four_port_hosts();
$fourporthosst = array();
foreach ($host_results as $ret) {
	$fourporthosts[] = new Host($ret->host_id);
}

$template = $templates . 'dangerhost.tpl.php';

?>