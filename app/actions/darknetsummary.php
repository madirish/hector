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
require_once($approot . 'lib/class.Collection.php');
require_once($approot . 'lib/class.Darknet.php');

$bound = " AND d.received_at > DATE_SUB(NOW(), INTERVAL 7 DAY) ";

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
$dst_top_percent = $darknet->get_top_field_percent($field='dst_port',$bound=7);
$dst_top = (!empty($dst_top_percent)) ? key($dst_top_percent) : '';
$dst_percent = (isset($dst_top_percent[$dst_top])) ? $dst_top_percent[$dst_top] : 0;

// Country frequencies
$c_top_percent = $darknet->get_top_field_percent($field='country_code',$bound=7);
$c_top = (!empty($c_top_percent)) ? key($c_top_percent) : '';
$c_percent = (isset($c_top_percent[$c_top])) ? $c_top_percent[$c_top] : 0;

// IP frequencies
$ip_top_percent = $darknet->get_top_field_percent($field='src_ip',$bound=7);
$ip_top = (!empty($ip_top_percent)) ? key($ip_top_percent) : '';
$ip_percent = (isset($ip_top_percent[$ip_top])) ? $ip_top_percent[$ip_top] : 0;

// Protocol frequencies
$proto_top_percent = $darknet->get_top_field_percent($field='proto',$bound=7);
$proto_top = (!empty($proto_top_percent)) ? key($proto_top_percent) : '';
$proto_percent = (isset($proto_top_percent[$proto_top])) ? $proto_top_percent[$proto_top] : 0;

hector_add_js('darknetsummary.js');

if (! isset($_GET['ajax']) && ! isset($ajax)) {
    include_once($templates. 'admin_headers.tpl.php');
}
include_once($templates . 'darknetsummary.tpl.php');
?>