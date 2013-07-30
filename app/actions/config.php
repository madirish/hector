<?php
/**
 * This is the default subcontroller for assets
 */

$object = isset($_GET['object']) ? $_GET['object'] : '';

if ($object == 'supportgroup') {
	$_GET['object'] = 'supportgroup';
	$header = "Support Group";
	include_once($approot . 'actions/details.php');
}

elseif ($object == 'hostgroups') {
	$_GET['object'] = 'host_group';
	$header = "Host Group";
	include_once($approot . 'actions/details.php');
}

/*elseif ($object == 'host_group') {
	$_GET['object'] = 'host_group';
	include_once($approot . 'actions/details.php');
}*/
elseif ($object == 'scan') {
	$_GET['object'] = 'scan';
	$header = 'Scheduled Scan';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'scan_type') {
	$_GET['object'] = 'scan_type';
	$header = 'Scan Script';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'reports') {
	include_once($approot . 'actions/reports.php');
}
elseif ($object == 'users') {
	$_GET['object'] = 'user';
	include_once($approot . 'actions/users.php');
}
elseif ($object == 'tags') {
	$_GET['object'] = 'tag';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'location') {
	$_GET['object'] = 'location';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'feeds') {
	$_GET['object'] = 'feed';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'add_hosts') {
	$_GET['object'] = 'add_hosts';
	include_once($approot . 'actions/add_hosts.php');
}
elseif ($object == 'api_key') {
	$_GET['object'] = 'api_key';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'vuln') {
	$_GET['object'] = 'vuln';
	include_once($approot . 'actions/details.php');
}
else {
	include_once($templates. 'admin_headers.tpl.php');
	include_once($templates . 'config.tpl.php');
}


?>