<?php
/**
 * This is the default subcontroller to show OSSEC alerts
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Move the SQL out of this file and into a class
 */

/**
 * Require the database
 */
require_once($approot . 'lib/class.Host.php');


$javascripts .= "<script type='text/javascript' src='js/ossec.js'></script>\n";



$ossec = new Host();
$host_ids = $ossec->get_ossec_host_ids(); 
$hosts = array();
foreach($host_ids as $host_id) {
	$host = new Host($host_id);
	$hosts[] = $host->get_object_as_array();
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'ossec.tpl.php');

?>