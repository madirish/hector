<?php
/**
 * Show darknet probes
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * 
 */

/**
 * Setup defaults.
 */
$content = '';
require_once($approot . 'lib/class.Darknet.php');

$bound = " AND d.received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) ";

$results = array();

if (isset($_GET['country'])) {
    $country = substr($_GET['country'], 0, 2);
    $country = strtoupper($country);
    $country = mysql_real_escape_string($country);
	$filter = $bound . " AND d.country_code = '$country'";
    $results = new Collection('Darknet', $filter);
}
else {
	$results = new Collection('Darknet', $bound);
}
$darknets = array();
if (is_array($results->members)) {
	$darknets = $results->members;
}



$darknet = new Darknet();


// Destination port frequencies
$dst_frequencies = $darknet->get_field_frequencies($field='dst_port',$bound);
$dst_top = key($dst_frequencies);
$dst_frequency = $dst_frequencies[$dst_top];
$dst_total = array_sum($dst_frequencies);
$dst_percent = round(($dst_frequency / $dst_total) * 100);

// Country frequencies
$country_frequencies = $darknet->get_field_frequencies($field='country_code',$bound);
$c_top = key($country_frequencies);
$c_frequency = $country_frequencies[$c_top];
$c_total = array_sum($country_frequencies);
$c_percent = round(($c_frequency / $c_total) * 100);

// IP frequencies
$ip_frequencies = $darknet->get_field_frequencies($field='src_ip',$bound);
$ip_top = key($ip_frequencies);
$ip_frequency = $ip_frequencies[$ip_top];
$ip_total = array_sum($ip_frequencies);
$ip_percent = round(($ip_frequency / $ip_total) * 100);

// Protocol frequencies
$proto_frequencies = $darknet->get_field_frequencies($field='proto',$bound);
$proto_top = key($proto_frequencies);
$proto_frequency = $proto_frequencies[$proto_top];
$proto_total = array_sum($proto_frequencies);
$proto_percent = round(($proto_frequency / $proto_total) * 100);


$javascripts .= '<script type="text/javascript" charset="utf8" src="js/jquery.dataTables.js"></script>' . "\n";
$javascripts .= '<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">' . "\n";

if (! isset($_GET['ajax']) && ! isset($ajax)) {
    include_once($templates. 'admin_headers.tpl.php');
}
include_once($templates . 'darknetsummary.tpl.php');
?>