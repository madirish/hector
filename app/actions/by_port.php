<?php
/**
 * Advanced search controller
 * now with insanely complex queries!
 * 
 * by Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * Last updated 18 October, 2012
 */

$tagsin = '';
$tagsex = '';
if (isset($_POST['tagsin'])) {
	$tagsin = (is_array($_POST['tagsin'])) ? join(',',array_map("intval",$_POST['tagsin'])) : intval($_POST['tagsin']);
}
if (isset($_POST['tagsex'])) {
	$tagsex = (is_array($_POST['tagsex'])) ? join(',',array_map("intval",$_POST['tagsex'])) : intval($_POST['tagsex']);
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

if ($anyports != 0 || $allports != 0) { 
	if ($anyports != 0 && $allports == 0) {
		$content .= '<h3>Results with any ports: ' . $anyports;
		if ($portsex != 0) $content .= ' excluding ports: ' . $portsex;
		$content .= '</h3>';
		if ($portsex == 0) {
		  $query = 'select distinct(nsr.host_id), h.supportgroup_id, h.host_id, ' .
		  		'nsr.nmap_scan_result_timestamp ' .
		  		'from nmap_scan_result nsr, state s, host h ';
		  if (! (isset($appuser) && $appuser->get_is_admin())) { $query .= ', user_x_support_group ux ';}		
		  if ($tagsin != '' || $tagsex != '') {
		  	$query .= ' LEFT OUTER JOIN host_x_tag x on x.host_id = h.host_id ';
		  	if ($tagsin != '') $query .= ' AND x.tag_id IN (' . $tagsin . ') ';
		  	if ($tagsex != '') $query .= ' AND x.tag_id NOT IN (' . $tagsex . ') ';
		  }
			$query .= 'WHERE nsr.host_id = h.host_id AND ' .
					'h.host_id = nsr.host_id and ' .
					's.state_id = nsr.state_id AND ' .
					'nsr.nmap_scan_result_port_number IN (' . $anyports. ') ';
			$query .= ' and s.state_state = \'open\' ';
			if (! (isset($appuser) && $appuser->get_is_admin())) {
				$query .= ' AND ux.supportgroup_id = h.supportgroup_id and ' .
					'ux.user_id = ' . $appuser->get_id() . ' ';
			}
			if ($tagsex != '') $query .= ' AND x.host_id IS NULL';
			if ($tagsin != '') $query .= ' AND x.host_id IS NOT NULL ';
			$query .= ' order by h.supportgroup_id';
			$host_results = $db->fetch_object_array($query);
		}
		else {
			$query = 'select distinct(nsr1.host_id) from nmap_scan_result nsr1';
			if (! (isset($appuser) && $appuser->get_is_admin())) { $query .= ', host h, user_x_support_group ux ';}		
			$query .= ' left outer join nmap_scan_result nsr2 on nsr1.host_id = nsr2.host_id ';
			if ($tagsin != '' || $tagsex != '') {
				$query .= ' LEFT OUTER JOIN host_x_tag x on nsr1.host_id = x.host_id ';
		  	if ($tagsin != '') $query .= ' AND x.tag_id IN (' . $tagsin . ') ';
		  	if ($tagsex != '') $query .= ' AND x.tag_id NOT IN (' . $tagsex . ') ';
			}
			$query .= ' where nsr1.nmap_scan_result_port_number IN (' . $anyports . ') ';
			$query .= ' AND nsr1.state_id = 1 AND nsr2.nmap_scan_result_port_number IN (' . $portsex . ') ';
			$query .= ' AND nsr2.state_id != 1';
			if (! (isset($appuser) && $appuser->get_is_admin())) { 
				$query .= ' and nsr1.host_id = h.host_id ';
				$query .= ' AND ux.supportgroup_id = h.supportgroup_id and ' .
					'ux.user_id = ' . $appuser->get_id() . ' ';
			}		
			if ($tagsex != '') $query .= ' AND x.host_id IS NULL';
			if ($tagsin != '') $query .= ' AND x.host_id IS NOT NULL';
			$host_results = $db->fetch_object_array($query);
		}
	}
	else if ($anyports == 0 && $allports != 0) { 
		// ToDo:  Add access restrictions and tag exclusion/inclusions
		$content .= '<h3>Results with all ports: ' . $allports;
		if ($portsex != 0) $content .= ' excluding ports: ' . $portsex;
		$content .= '</h3>';
		$ports = explode(',', $allports);
		$exports = $portsex != 0 ? explode(',',$portsex) : 0;
		$i = 2;
		$count = count($ports);
		$query = 'select nsr1.host_id, max(nsr1.nmap_scan_result_timestamp) from nmap_scan_result nsr1';
		while ($i <= $count) {
			$query .= ' inner join nmap_scan_result nsr' . $i;
			$query .= ' on nsr1.host_id = nsr' . $i . '.host_id ';
			$i++;
		}
		if ($exports != 0) {
			while ($i <= $count + count($exports)) {
				$query .= ' inner join nmap_scan_result nsr' . $i;
				$query .= ' on nsr1.host_id = nsr' . $i . '.host_id ';
				$i++;;
			}
		}
		$query .= ' where nsr1.nmap_scan_result_port_number = ' . intval($ports[0]);
		$query .= ' and nsr1.state_id = 1 ';
		$i = 2;
		while ($i <= $count) {
			$query .= ' and nsr' . $i . '.nmap_scan_result_port_number = ' . intval($ports[$i-1]);
			$query .= ' and nsr' . $i . '.state_id = 1 ';
			$i++;
		}
		if ($exports != 0) {
			$exportscount = 0;
			while ($i <= $count + count($exports)) {
				$query .= ' and nsr' . $i . '.nmap_scan_result_port_number = ' . intval($exports[$exportscount]);
				$query .= ' and nsr' . $i . '.state_id != 1 ';
				$exportscount++;
				$i++;
			}
		}
		$host_results = $db->fetch_object_array($query);
	}
	else { // Got all the criteria
		$content .= '<h3>Results with all ports: ' . $allports;
		$content .= ' with any ports: ' . $anyports;
		if ($portsex != 0) $content .= ' excluding ports: ' . $portsex;
		$content .= '</h3>';
		
		// Select the machines with any of the requested ports, minus exclusion
		$query = ' select distinct(nsr1.host_id) from nmap_scan_result nsr1 ';
		if ($portsex != 0) {
			$query .= ' LEFT JOIN nmap_scan_result nsrex ON nsr1.host_id = nsrex.host_id ';
			$query .= ' AND nsrex.nmap_scan_result_port_number IN (' . $portsex . ') ';
			$query .= ' AND nsrex.state_id = 1 ';
		}
		$query .= ' where nsr1.nmap_scan_result_port_number IN (' . $anyports . ') and nsr1.state_id = 1';
		if ($portsex != 0) {
			$query .= ' AND nsrex.host_id IS NULL';
		}
		$hosts = $db->fetch_object_array($query);
		$host_ids = '';
		foreach($hosts as $host) $host_ids .= $host->host_id . ',';
		$host_ids = substr($host_ids, 0, -1); // Chop off the last comma
		
		// Now get the intersection of that list and the hosts with allports
		$ports = explode(',', $allports);
		$ports = array_map(intval, $ports);  // type safety
		$count = count($ports);
		$i = 1;
		$query = 'select nsr1.host_id from nmap_scan_result nsr1 ';
		while ($i < $count) {
			$query .= ' INNER JOIN nmap_scan_result nsr' . $i++ . ' ON nsr1.host_id = nsr' . $i . '.host_id ';
		}
		$i = 1;	
		$query .= ' where nsr1.host_id IN ('. $host_ids . ') AND ';
		$query .= ' nsr1.nmap_scan_result_port_number = ' . $ports[0];
		$query .= ' AND nsr1.state_id = 1 ';
		while ($i < $count) {
			$query .= ' AND nsr' . $i++ . '.nmap_scan_result_port_number = ' . $ports[$i-1];
		}
		$host_results = $db->fetch_object_array($query);
	}
	if (is_array($host_results)) $content .= '<h4>' . count($host_results) . ' records found</h4>';
	$content .= '<table id="table' . $port . '" class="table table-striped">';
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
					'<td>' . $ret->nmap_scan_result_timestamp . '</td></tr>';
		}
		if (count($host_results) < 1) {
			$content .= '<tr><td colspan="4">No results available ' .
					'(you may not have permissions to see specific hosts).</td></tr>';
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