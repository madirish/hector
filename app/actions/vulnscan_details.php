<?php
/**
 * This is the default subcontroller for runs of a specific vulnerability scan
 * reports
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 */

/**
 * Require the factory class
 */
require_once($approot . 'lib/class.Vulnscan.php');
$scanid = isset($_GET['id']) ? $_GET['id'] : null;
$datetime = isset($_GET['datetime']) ? $_GET['datetime'] : null;
$vulnscan = null;
if (isset($_GET['id']) && isset($_GET['datetime'])) {
	$vulnscan = new Vulnscan($scanid, $datetime);
}

include_once($templates . 'admin_headers.tpl.php');
include_once($templates . 'vulnscan_details.tpl.php');
?>