<?php
/**
 * This is the default subcontroller for handing off
 * to alerts, search, or reports.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Deprecate this entire page.
 */

/**
 * Figure out what subcontroller to hand off to.
 */
$object = isset($_GET['object']) ? $_GET['object'] : '';

if ($object == 'alerts') {
	include_once($approot . 'actions/alerts.php');
}
elseif ($object == 'search') {
	include_once($approot . 'actions/search.php');
}
elseif ($object == 'ports') {
	$_GET['report'] = 'by_port';
	include_once($approot . 'actions/reports.php');
}
else {
	include_once($templates. 'admin_headers.tpl.php');
	include_once($templates . 'assets.tpl.php');
}


?>