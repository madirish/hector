<?php
/**
 * Advanced search controller
 * now with insanely complex queries!
 * 
 * by Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * Last updated 1 August 2014
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Move the SQL out of this file into a utility class!!!
 */

/**
 * Set up sane defaults
 */
$tagsin = '';
$tagsex = '';
if (isset($_POST['tagsin'])) {
	$tagsin = (is_array($_POST['tagsin'])) ? join(',',array_map("intval",$_POST['tagsin'])) : 0;
}
if (isset($_POST['tagsex'])) {
	$tagsex = (is_array($_POST['tagsex'])) ? join(',',array_map("intval",$_POST['tagsex'])) : 0;
}

$content = '';
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Supportgroup.php');

// Bridge from older code
if (isset($_GET['ports'])) {
	// Link from home like ?action=reports&report=by_port&ports=22/tcp
	if ($pos = stripos($_GET['ports'], '/tcp')){
		$_POST['allports'] = substr($_GET['ports'],0,$pos);
	}
	// Link from home like ?action=reports&report=by_port&ports=53/udp
	if ($pos = stripos($_GET['ports'], '/udp')){
		$_POST['allUDPports'] = substr($_GET['ports'],0,$pos);
	}
}

// Allow simple get requests as well
if (isset($_GET['anyports'])) $_POST['anyports'] = $_GET['anyports'];
if (isset($_GET['allports'])) $_POST['allports'] = $_GET['allports'];
if (isset($_GET['portsex'])) $_POST['portsex'] = $_GET['portsex'];
if (isset($_GET['anyUDPports'])) $_POST['anyUDPports'] = $_GET['anyUDPports'];
if (isset($_GET['allUDPports'])) $_POST['allUDPports'] = $_GET['allUDPports'];
if (isset($_GET['UDPportsex'])) $_POST['UDPportsex'] = $_GET['UDPportsex'];


$allports = isset($_POST['allports']) ? preg_replace('/^(d|,)*/','',$_POST['allports']) : 0;
$anyports = isset($_POST['anyports']) ? preg_replace('/^(d|,)*/','',$_POST['anyports']) : 0;
$portsex = isset($_POST['portsex']) ? preg_replace('/^(d|,)*/','',$_POST['portsex']) : 0;
$allUDPports = isset($_POST['allUDPports']) ? preg_replace('/^(d|,)*/','',$_POST['allUDPports']) : 0;
$anyUDPports = isset($_POST['anyUDPports']) ? preg_replace('/^(d|,)*/','',$_POST['anyUDPports']) : 0;
$UDPportsex = isset($_POST['UDPportsex']) ? preg_replace('/^(d|,)*/','',$_POST['UDPportsex']) : 0;
$db = Db::get_instance();

// Select all hosts into a temp table, then winnow it down for results, then strip based on perms
$query = 'CREATE TEMPORARY TABLE ' .
		'tmp_search (host_id INT NOT NULL PRIMARY KEY)';
$db->iud_sql($query);
$tempTablePopulated = 0;

if ($anyports != 0) {
	$query = 'INSERT INTO tmp_search (' .
				'SELECT distinct(host_id) ' .
				'FROM nmap_result ' .
				'WHERE nmap_result_port_number in (' . $anyports . ') ' .
				'AND lower(nmap_result_protocol) = "tcp" AND state_id = 1) ';
	$db->iud_sql($query);
	$tempTablePopulated = 1;
}
if ($anyUDPports != 0) {
	$query = 'INSERT INTO tmp_search (' .
				'SELECT DISTINCT(host_id) ' .
				'FROM nmap_result ' .
				'WHERE nmap_result_port_number IN (' . $anyUDPports . ') ' .
				'AND lower(nmap_result_protocol) = "udp" AND state_id = 1) ';
	$db->iud_sql($query); 
	$tempTablePopulated = 1;
}
// Only TCP based all-ports requirement
if ($allports != 0 && $allUDPports == 0) { 
	$hosts = array(); 
	$ports_num = explode(',', $allports);
	foreach ($ports_num as $port) {
		$query = 'SELECT host_id ' .
				'FROM nmap_result ' .
				'WHERE nmap_result_port_number = ' . intval($port) . ' ' .
				'AND LOWER(nmap_result_protocol) = "tcp" ' .
				'AND state_id = 1';
		$results = $db->fetch_object_array($query);
		$query_results = array();
		foreach ($results as $id) $query_results[] = $id->host_id;
		// First results, push them into the array
		if (count($hosts) < 1) {
			foreach ($query_results as $id) $hosts[] = $id;
		}
		// more results - if the host from the array aren't in the results, remove it
		else {
			foreach ($hosts as $host) {
				if (! in_array($host, $query_results))  unset($hosts[array_search($host, $hosts)]);
			}
		}
	}
	// Drop results from the search table if they aren't in our list
	if ($tempTablePopulated == 1) {
		$query = 'DELETE FROM tmp_search ' .
				'WHERE host_id NOT IN (' . join(',', $hosts) . ')';
		$db->iud_sql($query);
	}
	foreach ($hosts as $host) {
		$query = 'INSERT INTO tmp_search ' .
				'SET host_id = ' . $host . ' ' .
				'ON DUPLICATE KEY UPDATE host_id = ' . $host;
		$db->iud_sql($query);
	}
	$tempTablePopulated = 1;
}
// Only UDP based all-ports requirement
elseif ($allports == 0 && $allUDPports != 0) { 
	$hosts = array(); 
	$ports_num = explode(',', $allUDPports);
	foreach ($ports_num as $port) {
		$query = 'SELECT host_id ' .
				'FROM nmap_result ' .
				'WHERE nmap_result_port_number = ' . intval($port) . ' ' .
				'AND LOWER(nmap_result_protocol) = "udp" ' .
				'AND state_id = 1';
		$results = $db->fetch_object_array($query);
		$query_results = array();
		foreach ($results as $id) $query_results[] = $id->host_id;
		// First results, push them into the array
		if (count($hosts) < 1) {
			foreach ($query_results as $id) $hosts[] = $id;
		}
		// more results - if the host from the array aren't in the results, remove it
		else {
			foreach ($hosts as $host) {
				if (! in_array($host, $query_results))  unset($hosts[array_search($host, $hosts)]);
			}
		}
		
	}
	// Drop results from the search table if they aren't in our list
	if ($tempTablePopulated == 1) {
		$query = 'DELETE FROM tmp_search ' .
				'WHERE host_id NOT IN (' . join(',', $hosts) . ')';
		$db->iud_sql($query);
	}
	foreach ($hosts as $host) {
		$query = 'INSERT INTO tmp_search ' .
				'SET host_id = ' . $host . ' ' .
				'ON DUPLICATE KEY UPDATE host_id = ' . $host;
		$db->iud_sql($query);
	}
	$tempTablePopulated = 1; 
}
// Both TCP and UDP all-ports requirement
elseif ($allports != 0 && $allUDPports != 0) { 
	$hosts = array(); 
	$tcp_ports_num = $allports;
	$udp_ports_num = $allUDPports;

	$query = 'SELECT host_id ' .
			'FROM nmap_result ' .
			'WHERE (nmap_result_port_number IN (' . $tcp_ports_num . ') ' .
			'AND LOWER(nmap_result_protocol) = "tcp") ' .
			'AND (nmap_result_port_number IN (' . $udp_ports_num . ') ' .
			'AND LOWER(nmap_result_protocol) = "udp")' .
			'AND state_id = 1';
	$results = $db->fetch_object_array($query);
	$query_results = array();
	foreach ($results as $id) $query_results[] = $id->host_id;
	// First results, push them into the array
	if (count($hosts) < 1) {
		foreach ($query_results as $id) $hosts[] = $id;
	}
	// more results - if the host from the array aren't in the results, remove it
	else {
		foreach ($hosts as $host) {
			if (! in_array($host, $query_results))  unset($hosts[array_search($host, $hosts)]);
		}
	}
	
	// Drop results from the search table if they aren't in our list
	if ($tempTablePopulated == 1) {
		$query = 'DELETE FROM tmp_search ' .
				'WHERE host_id NOT IN (' . join(',', $hosts) . ')';
		$db->iud_sql($query);
	}
	foreach ($hosts as $host) {
		$query = 'INSERT INTO tmp_search ' .
				'SET host_id = ' . $host . ' ' .
				'ON DUPLICATE KEY UPDATE host_id = ' . $host;
		$db->iud_sql($query);
	}
	$tempTablePopulated = 1;
}
// TCP port exclusion
if ($portsex != 0) {
	if ($tempTablePopulated == 1) {
		$query = 'SELECT host_id FROM tmp_search';
		$results = $db->fetch_object_array($query);
		foreach (explode(',',$portsex) as $portnum) {
			foreach ($results->host_id as $id) {
				$query = 'SELECT COUNT(host_id) AS theCount ' .
						'FROM nmap_result ' .
						'WHERE nmap_result_port_number = ' . $portnum . ' ' .
						'AND LOWER(nmap_result_protocol) = "tcp" ' .
						'AND state_id = 1';
				$countquery = $db->fetch_object_array($query);
				if ($countquery->theCount > 0) {
					$db->iud_sql('delete from tmp_search where host_id = ' . $id);
				}
			}
		}
	}
	else {
		// This is gonna be a massive result!
		$query = 'INSERT INTO tmp_search ' .
				'(SELECT DISTINCT(host_id) FROM host)';
		$db->iud_sql($query);
		foreach (explode(',',$portsex) as $port) {
			$query = 'SELECT host_id ' .
					'FROM nmap_result ' .
					'WHERE nmap_result_port_number = ' . $port . ' ' .
					'AND nmap_result_protocol = "tcp" ' .
					'AND state_id = 1';
			$countquery = $db->fetch_object_array($query);
			foreach ($countquery as $host) {
				$db->iud_sql('DELETE FROM tmp_search WHERE host_id = ' . $host->host_id);
			}
		}
	}
	$tempTablePopulated = 1;
}
// UDP port exclusion
if ($UDPportsex != 0) {
	if ($tempTablePopulated == 1) {
		$query = 'select host_id from tmp_search';
		$results = $db->fetch_object_array($query);
		foreach (explode(',',$UDPportsex) as $portnum) {
			foreach ($results->host_id as $id) {
				$query = 'SELECT COUNT(host_id) AS theCount ' .
						'FROM nmap_result ' .
						'WHERE nmap_result_port_number = ' . $portnum . ' ' .
						'AND LOWER(nmap_result_protocol) = "udp" ' .
						'AND state_id = 1';
				$countquery = $db->fetch_object_array($query);
				if ($countquery->theCount > 0) {
					$db->iud_sql('DELETE FROM tmp_search WHERE host_id = ' . $id);
				}
			}
		}
	}
	else {
		// This is gonna be a massive result!
		$query = 'INSERT INTO tmp_search ' .
				'(SELECT DISTINCT(host_id) FROM host)';
		$db->iud_sql($query);
		foreach (explode(',',$UDPportsex) as $port) {
			$query = 'SELECT host_id ' .
					'FROM nmap_result ' .
					'WHERE nmap_result_port_number = ' . $port . ' ' .
					'AND LOWER(nmap_result_protocol = "udp") ' .
					'AND state_id = 1';
			$countquery = $db->fetch_object_array($query);
			foreach ($countquery as $host) {
				$db->iud_sql('DELETE FROM tmp_search WHERE host_id = ' . $host->host_id);
			}
		}
	}
	$tempTablePopulated = 1;
}

// Apply the tagging
if ($tagsin != 0) {
	if ($tempTablePopulated == 1) {
		$hosts = $db->fetch_object_array('select host_id from tmp_search');
		foreach ($hosts as $host) {
			foreach (explode(',',$tagsin) as $tag) {
				$query = 'select count(host_id) as theCount from host_x_tag where host_id = ' . $host->id . ' and tag_id = ' . $tag;
				if ($db->fetch_object_array($query)->theCount == 0) {
					$db->iud_sql('delete from tmp_search where host_id = ' . $host->id);
				}
			}
		}
	}
	else {
		$db->iud_sql('insert into tmp_search (select host_id from host_x_tag where tag_id IN (' . $tagsin . '))');
		$tempTablePopulated = 1;
	}
}
if ($tagsex != 0) {
	if ($tempTablePopulated == 1) {
		$hosts = $db->fetch_object_array('select host_id from tmp_search');
		foreach ($hosts as $host) {
			foreach (explode(',',$tagsex) as $tag) {
				$query = 'select count(host_id) as theCount ' .
						'from host_x_tag ' .
						'where host_id = ' . $host->id . ' and tag_id = ' . $tag;
				if ($db->fetch_object_array($query)->theCount > 0) {
					$db->iud_sql('delete from tmp_search where host_id = ' . $host->id);
				}
			}
		}
	}
	else {
		$db->iud_sql('insert into tmp_search (select host_id from host)');
		$query = 'select host_id from host_x_tag where tag_id IN (' . $tagsex . ')';
		$results = $db->fetch_object_array($query);
		if (is_array($results)) {
			foreach ($results as $host) {
				$db->iud_sql('delete from tmp_search where host_id = ' . $host->host_id);
			}
		}
		$tempTablePopulated = 1;
	}
}
// Apply permissions (if applicable) 
if (! (isset($appuser) && $appuser->get_is_admin())) {
	$hosts = $db->fetch_object_array('select host_id from tmp_search');
	$query = 'select supportgroup_id from user_x_supportgroup where user_id = ' . $appuser->get_id();
	$supp_groups = array();
	foreach ($db->fetch_object_array($query) as $group) {
		$supp_groups[] = $group->supportgroup_id;
	}
	foreach ($hosts as $host) {
		$query = 'select supportgroup_id from host where host_id = ' . $host->host_id;
		$groups = $db->fetch_object_array($query);
		$group = $groups[0]->supportgroup_id;
		if (! in_array($group, $supp_groups)) {
			$db->iud_sql('delete from tmp_search where host_id = ' . $host->id);
		}
	}
}

// if this is the results page
if ($tempTablePopulated > 0) {
	$sql = 'SELECT max(n.nmap_result_timestamp) AS maxtime, t.host_id ' .
			'FROM tmp_search t, nmap_result n ' .
			'WHERE n.host_id = t.host_id ' .
			'GROUP BY t.host_id';
	$host_results = $db->fetch_object_array($sql);
	if (count(array_keys($host_results)) < 1) $host_results = null;
    $search_results = array();
	if (is_array($host_results)) {
		foreach ($host_results as $ret) {
            $tmphost = new Host($ret->host_id);
            $tmphost->maxtime = $ret->maxtime;
            $search_results[] = $tmphost;
		}
	}
    $hgcollection = new Collection('Host_group');
    $hostgroups = array();
    if (is_array($hgcollection->members)) {
    	foreach ($hgcollection->members as $group) $hostgroups[] = $group;
    }

    require_once($approot . 'lib/class.Form.php');
    $form = new Form();
    $formname = 'searchResultsToHostGroup';
    $form->set_name($formname);
    $token = $form->get_token();
    $form->save();
}
else {
	
	require_once($approot . 'lib/class.Form.php');
	require_once($approot . 'lib/class.Tag.php');
	require_once($approot . 'lib/class.Collection.php');
	$collection = new Collection('Tag');
	$tags = array();
	if (is_array($collection->members) && isset($collection->members)) {
		$tags = $collection->members;
	}
	$form = new Form();
	$formname = 'port_search_form';
	$form->set_name($formname);
	$token = $form->get_token();
	$form->save();
}

?>