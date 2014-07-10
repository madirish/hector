<?php
/**
 * Subcontroller for adding new hosts to the system.
 * Diverging from the normal MVC model to drop hosts
 * directly into the database.  This is because 
 * constructing a full host, modifying it, then saving
 * it takes a lot of overhead and if we're adding a
 * few hundred hosts (say at initial setup) this can
 * take forever.
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @version 2013.08.29
 */

/**
 * Requre the Form for XSRF protection
 */ 
require_once($approot . 'lib/class.Form.php');
include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.Host_group.php');
require_once($approot . 'lib/class.BulkHostAdder.php');

if (isset($_POST['startip'])) {
	// Add hosts to the database
	$adder = new BulkHostAdder();
    
	$hostgroups = array();
	if (isset($_POST["newhostgroup"]) && $_POST["newhostgroup"] !== '') {
		$group = new Host_group();
		$group->set_name($_POST["newhostgroup"]);
		$group->save();
		$hostgroups[] = $group->get_id();
	}
	if (isset($_POST['hostgroup'])) {
		if (is_array($_POST['hostgroup'])) {
			foreach ($_POST['hostgroup'] as $group) {
				$hostgroups[] = $group;
			}	
		}
		else {
			$hostgroups[] = $_POST['hostgroup'];
		}
	}
	
    if ($adder->add_by_IP($_POST['startip'], $_POST['endip'], $hostgroups)) {
    	$message = "Hosts added.";
    }
	else {
		$message = $adder->get_error();
	}
}

$collection = new Collection('Host_group');
$hostgroups = array();
if (isset($collection->members) && is_array($collection->members)) {
	foreach ($collection->members as $group) {
		$hostgroups[$group->get_id()] = $group->get_name();
	}
}

$form = new Form();
$form_name = 'add_hosts';
$form->set_name($form_name);
$token = $form->get_token();
$form->save();


if (! isset($_GET['ajax'])) include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'add_hosts.tpl.php');

?>