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
 * @version 2013.08.28
 */
 
require_once($approot . 'lib/class.Form.php');

include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.Host_group.php');

if (isset($_POST['startip'])) {
	// Add hosts to the database
	$startip = ip2long($_POST['startip']);
	$endip = isset($_POST['endip']) && $_POST['endip'] != '' ? ip2long($_POST['endip']) : $startip;
	if ($endip < $startip) {
		$message = "Start IP must be less than end IP.";
	}
	else {
		$db = Db::get_instance();
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
		$ip = $startip;
		while ($ip <= $endip) {
			// Don't save 192.168.2.0 for instance
			// Probably a better mathy way to do this ($ip%8 == 0) ?
			if (substr(long2ip($ip), -2) != ".0") {
				// Check for duplicate entries
				$sql = array(
					'SELECT host_id FROM host WHERE host_ip_numeric = ?i',
					$ip
				);	
				$result = $db->fetch_object_array($sql);
				$id = isset($result[0]->host_id) ? $result[0]->host_id : 0;
				
				// If the host is new add it
				if ($id < 1) {
					$sql = array(
						'INSERT INTO host SET host_ip = INET_NTOA(\'?i\'), host_ip_numeric = ?i',
						$ip, $ip
					);
					$db->iud_sql($sql);
			    	// Now set the id
			    	$id = mysql_insert_id();	
			    	// Insert the host groups
			    	foreach ($hostgroups as $group_id) {
			    		$sql = array(
							'INSERT INTO host_x_host_group SET host_id = ?i, host_group_id = ?i',
							$id, $group_id
						);
						$db->iud_sql($sql);
			    	}
				}
				// if this record already exists just set up the hostgruops
				else {
					foreach ($hostgroups as $group_id) {
						$sql = array(
							'SELECT host_group_id FROM host_group WHERE host_group_id = ?i AND host_id = ?i',
							$group_id, $id
						);	
						$result = $db->fetch_object_array($sql);
						$host_group_id = isset($result[0]->host_group_id) ? $result[0]->host_group_id : 0;
						if ($host_group_id < 1) {
							$sql = array(
								'INSERT INTO host_x_host_group SET host_id = ?i, host_group_id = ?i',
								$id, $group_id
							);
							$db->iud_sql($sql);
						}
					}
					
				}
			}
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