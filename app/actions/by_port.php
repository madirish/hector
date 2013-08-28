<?php
/**
 * Advanced search controller
 * now with insanely complex queries!
 * 
 * by Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * Last updated 18 October, 2012
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Move the SQL out of this file into a utility class
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
if (isset($_GET['ports'])) $_POST['allports'] = $_GET['ports'];

// Allow simple get requests as well
if (isset($_GET['anyports'])) $_POST['anyports'] = $_GET['anyports'];
if (isset($_GET['allports'])) $_POST['allports'] = $_GET['allports'];
if (isset($_GET['portsex'])) $_POST['portsex'] = $_GET['portsex'];


$allports = isset($_POST['allports']) ? preg_replace('/^(d|,)*/','',$_POST['allports']) : 0;
$anyports = isset($_POST['anyports']) ? preg_replace('/^(d|,)*/','',$_POST['anyports']) : 0;
$portsex = isset($_POST['portsex']) ? preg_replace('/^(d|,)*/','',$_POST['portsex']) : 0;
$db = Db::get_instance();

// Select all hosts into a temp table, then winnow it down for results, then strip based on perms
$query = 'create temporary table tmp_search (host_id INT NOT NULL PRIMARY KEY)';
$db->iud_sql($query);
$tempTablePopulated = 0;

if ($anyports != 0) {
	$query = 'insert into tmp_search (' .
				'select distinct(host_id) ' .
				'from nmap_result ' .
				'where nmap_result_port_number in (' . $anyports . ') ' .
				'AND state_id = 1) ';
	$db->iud_sql($query);
	$tempTablePopulated = 1;
}
if ($allports != 0) { 
	$hosts = array(); 
	$ports_num = explode(',', $allports);
	foreach ($ports_num as $port) {
		$query = 'select host_id from nmap_result ' .
				'where nmap_result_port_number = ' . intval($port) . ' and state_id = 1';
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
		$query = 'delete from tmp_search where host_id not in (' . join(',', $hosts) . ')';
		$db->iud_sql($query);
	}
	foreach ($hosts as $host) {
		$query = 'insert into tmp_search set host_id = ' . $host . ' ON DUPLICATE KEY UPDATE host_id = ' . $host;
		$db->iud_sql($query);
	}
	$tempTablePopulated = 1;
}
if ($portsex != 0) {
	if ($tempTablePopulated == 1) {
		$query = 'select host_id from tmp_search';
		$results = $db->fetch_object_array($query);
		foreach (explode(',',$portsex) as $portnum) {
			foreach ($results->host_id as $id) {
				$query = 'select count(host_id) as theCount ' .
						'from nmap_result ' .
						'where nmap_result_port_number = ' . $portnum . ' and state_id = 1';
				$countquery = $db->fetch_object_array($query);
				if ($countquery->theCount > 0) {
					$db->iud_sql('delete from tmp_search where host_id = ' . $id);
				}
			}
		}
	}
	else {
		// This is gonna be a massive result!
		$query = 'insert into tmp_search (select distinct(host_id) from host)';
		$db->iud_sql($query);
		foreach (explode(',',$portsex) as $port) {
			$query = 'select host_id ' .
					'from nmap_result ' .
					'where nmap_result_port_number = ' . $port . ' and state_id = 1';
			$countquery = $db->fetch_object_array($query);
			foreach ($countquery as $host) $db->iud_sql('delete from tmp_search where host_id = ' . $host->host_id);
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
	$host_results = $db->fetch_object_array('select host_id from tmp_search');
	if (count(array_keys($host_results)) < 1) $host_results = null;
	$content .= '<h4>' . count($host_results) . ' records found</h4>';
	$content .= '<table id="table-port-result" class="table table-striped">';
	$content .= '<thead>' .
			'<tr><th>Hostname</th>' .
			'<th>Support Group</th>' . 
			'<th>IP Address</th>' .
			'<th>Last seen on:</th>' .
			'</tr></thead>' .
			'<tbody>';
	if (is_array($host_results)) {
		foreach ($host_results as $ret) {
			$host = new Host($ret->host_id);
			$supportgroup = new Supportgroup($host->get_supportgroup_id());
			$content .= '<tr><td>' . $host->get_name_linked() . '</td>' .
					'<td>' . $supportgroup->get_name() . '</td>' .
					'<td>' . $host->get_ip() . '</td>' .
					'<td>' . $ret->nmap_result_timestamp . '</td></tr>';
		}
		if (count($host_results) < 1) {
			$content .= '<tr><td colspan="4">No results available ';
			if (! $appuser->get_is_admin()) $content .=	'(you may not have permissions to see specific hosts).';
			$content .= '</td></tr>';
		}
	}
	$content .= '</tbody></table>';	
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