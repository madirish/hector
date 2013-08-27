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
		$hostgroups = array();
		if (isset($_POST["newhostgroup"]) && $_POST["newhostgroup"] !== '') {
			$group = new Host_group();
			$group->set_name($_POST["newhostgroup"]);
			$group->save();
			$hostgroups[] = $group->get_id();
		}
		$ip = $startip;
		while ($ip <= $endip) {
			$host = new Host('', 'yes'); // Minimal construction
			$ip_addr = long2ip($ip);
			$host->set_ip($ip_addr);
			$host->set_name(gethostbyaddr($ip_addr));
			$host->lookup_by_ip(); // Be sure no dupes exist
			if (isset($_POST['hostgroup'])) {
				foreach ($_POST['hostgroup'] as $group) {
					$hostgroups[] = $group;
				}	
			}
			// If the host groups already set append to them
			if (is_array($host->get_host_group_ids())) {
				$existing = $host->get_host_group_ids();
				$ecount = count($existing);
				foreach ($hostgroups as $group) {
					if (! array_search($group, $existing)) {
						$existing[] = $group;
					}
				}
				if (count($existing) > $ecount) {
					$host->set_host_group_ids($existing);
				}
			}
			// Otherwise simply set the new host groups
			else {
				$host->set_host_group_ids($hostgroups);
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