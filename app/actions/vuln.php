<?php
/**
 * This is the default subcontroller for vulnerability
 * management
 */

if (isset($_GET['object'])) {
	$_GET['report'] = $_GET['object'];
	include_once($approot . 'actions/reports.php');
}
else {
	include_once($templates. 'admin_headers.tpl.php');
	include_once($templates . 'vuln.tpl.php');
}


?>