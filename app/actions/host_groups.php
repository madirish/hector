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


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'host_groups.tpl.php');
?>