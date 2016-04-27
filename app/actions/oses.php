<?php
/** 
 * This is the report subcontroller for OS reporting
 * @author Justin Klein Keane <justin@madirish.net>
 * @package HECTOR
 */

/**
 * Require the factory class
 */
include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.Host.php');

$hosts_by_os = array();
$oses = array();
$hosts_by_os_collection = new Collection('Host', 'AND host_os IS NOT NULL AND host_os != ""');
if (isset($hosts_by_os_collection->members) && is_array($hosts_by_os_collection->members)) {
	$hosts_by_os = $hosts_by_os_collection->members;
	foreach ($hosts_by_os as $host) {
		if (array_key_exists($host->get_os_type(), $oses)) {
			$oses[$host->get_os_type()] += 1;
		}
		elseif (array_key_exists($host->get_os(), $oses)) {
			$oses[$host->get_os()] += 1;
		}
		else {
			if (! $host->get_os_type() == '') $oses[$host->get_os_type()] = 1;
			else $oses[$host->get_os()] = 1;
		}
	}
}

$labels = json_encode(array_keys($oses));
$data = json_encode(array_values($oses));

hector_add_js('oses.js');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'oses.tpl.php');

?>