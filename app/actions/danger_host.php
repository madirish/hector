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



$report = new Report();
$host_results = $report->get_seven_port_hosts();
$sevenporthosts = array();
foreach ($host_results as $result) {
	$sevenporthosts[] = new Host($result->host_id);
}

$host_results = $report->get_four_port_hosts();
$fourporthosts = array();
foreach ($host_results as $ret) {
	$fourporthosts[] = new Host($ret->host_id);
}

hector_add_js('danger_host.js');
$template = $templates . 'dangerhost.tpl.php';

?>