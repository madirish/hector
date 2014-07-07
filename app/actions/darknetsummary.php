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

if (! isset($_GET['ajax']) && ! isset($ajax)) {
    include_once($templates. 'admin_headers.tpl.php');
}
include_once($templates . 'darknetsummary.tpl.php');
?>