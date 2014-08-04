<?php
/**
 * This is the default subcontroller for displaying host groups
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Include the Alert class
 */
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Host_group.php');
require_once($approot . 'lib/class.Collection.php');

if (isset($_POST['hostgroup'])) {
	// Adding hosts from the search form
    $hostgroup = new Host_group($_POST['hostgroup']);
    foreach ($_POST['host_id'] as $host_id) {
    	$hostgroup->add_host_to_group($host_id);
    }
    $_GET['host_group_id'] = $_POST['hostgroup'];
}

if (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
	$hostgroup = new Host_group($_GET['id']);
    $hostgroup->delete();
    $message = 'Host group deleted!';
}

if (isset($_GET['host_group_id'])) {
    $hosts = array();
	$hostgroup = new Host_group($_GET['host_group_id']);
    $host_ids = $hostgroup->get_host_ids();
    foreach ($host_ids as $host_id) {
    	$hosts[] = new Host($host_id);
    }
}
else {
	$hostgroups = new Collection('Host_group');
    if (isset($hostgroups->members) && is_array($hostgroups->members)) {
        $hostgroups = $hostgroups->members;
    }
}

$javascripts .= '<script type="text/javascript" charset="utf8" src="js/jquery.dataTables.js"></script>';
$javascripts .= '<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">';

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'host_groups.tpl.php');
?>