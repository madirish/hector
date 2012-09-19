<?php

require_once($approot . 'lib/class.Form.php');

include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.Host.php');
include_once($approot . 'lib/class.Host_group.php');

if (isset($_POST['startip'])) {
	// Add hosts to the database
	$startip = ip2long($_POST['startip']);
	$endip = isset($_POST['endip']) && $_POST['endip'] != '' ? ip2long($_POST['endip']) : $startip;
	if ($endip < $startip) {
		$message = "Start IP must be less than end IP.";
	}
	else {
		$ip = $startip;
		while ($ip <= $endip) {
			$host = new Host();
			$ip_addr = long2ip($ip);
			$host->set_ip($ip_addr);
			$host->set_name(gethostbyaddr($ip_addr));
			if (isset($_POST['hostgroup'])) {
				$host->set_host_group_ids(array($_POST['hostgroup']));
			}
			// Don't save 192.168.2.0 for instance
			if (substr($ip_addr, -2) != ".0") $host->save();
			$ip++;
		}
		$message = "Hosts added.";
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