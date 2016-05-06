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

$prefix = "";
$filter = "";
if (isset($_GET['host_group_id']) && isset($_GET['live'])) {
	$hosts = array();
	$hostgroup = new Host_group($_GET['host_group_id']);
	$host_ids = $hostgroup->get_live_host_ids();
	foreach ($host_ids as $host_id) {
		$hosts[] = new Host($host_id);
	}
	$prefix = "Live ";
	$filter = '<a href="?action=host_groups&host_group_id=' . $hostgroup->get_id() . '">Show all</a>';
}
elseif (isset($_GET['host_group_id'])) {
    $hosts = array();
	$hostgroup = new Host_group($_GET['host_group_id']);
    $host_ids = $hostgroup->get_host_ids();
    foreach ($host_ids as $host_id) {
    	$hosts[] = new Host($host_id);
    }
	$filter = '<a href="?action=host_groups&live=yes&host_group_id=' . $hostgroup->get_id() . '">Only show live hosts</a>';
}
else {
	$hostgroups = new Collection('Host_group');
    if (isset($hostgroups->members) && is_array($hostgroups->members)) {
        $hostgroups = $hostgroups->members;
    }
}

hector_add_js('host_groups.js');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'host_groups.tpl.php');
?>