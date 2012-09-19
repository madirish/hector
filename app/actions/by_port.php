<?php

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

// Allow simple get requests as well
if (isset($_GET['ports'])) $_POST['ports'] = $_GET['ports'];
if (isset($_GET['portsex'])) $_POST['portsex'] = $_GET['portsex'];

if (isset($_POST['ports'])) { 
	$ports = preg_replace('/^(d|,)*/','',$_POST['ports']);
	$portsex = isset($_POST['portsex']) ? preg_replace('/^(d|,)*/','',$_POST['portsex']) : 0;
	$db = Db::get_instance();
	//foreach (explode(',',$ports) as $port) {
	$content .= '<h3>Results for ports ' . $ports;
	if ($portsex != 0) $content .= ' excluding ports ' . $portsex;
	$content .= '</h3>';
	if ($portsex == 0) {
	  $query = 'select distinct(nsr.host_id), h.supportgroup_id, nsr.nmap_scan_result_timestamp ' .
	  		'from nmap_scan_result nsr, state s, user_x_supportgroup ux, host h ' .
	  		'LEFT OUTER JOIN host_x_tag x on x.host_id = h.host_id ';
		if ($tagsin != '') $query .= 'AND x.tag_id IN (' . $tagsin . ') ';	
		if ($tagsex != '') $query .= 'and (x.tag_id NOT IN (' . $tagsex . ') OR nsr.host_id NOT IN (select host_id from host_x_tag)) ';
		$query .= 'WHERE nsr.host_id = h.host_id AND ' .
				'h.host_id = nsr.host_id and ' .
				's.state_id = nsr.state_id AND ' .
				'nsr.nmap_scan_result_port_number IN (' . $ports . ') ';
		$query .= 'and s.state_state = \'open\' ';
		if (! (isset($appuser) && $appuser->get_is_admin())) {
			$query .= ' AND ux.supportgroup_id = h.supportgroup_id and ' .
				'ux.user_id = ' . $appuser->get_id() . ' ';
		}
		$query .= ' order by h.supportgroup_id';
		$host_results = $db->fetch_object_array($query);
	}
	else {
		// We're going to have to do some back flips to get the exclusive set
		// @TODO: Access restriction, tag restriction
		$query = 'create temporary table host_ports1 ' .
				'select n.host_id, n.port_number, n.nmap_scan_result_timestamp ' .
				'from nmap_scan_result n ' .
				'where n.nmap_scan_result_port_number IN (' . $ports . ') ' .
				'and n.state_id = 1;';
		$db->iud_sql($query);
		$query = 'create temporary table host_ports2 ' .
				'select n.host_id, n.port_number ' .
				'from nmap_scan_result n ' .
				'where n.nmap_scan_result_port_number IN (' . $portsex . ') ' .
				'and n.state_id = 1;';
		$db->iud_sql($query);
		$query = 'select t1.host_id, t1.nmap_scan_result_timestamp ' .
				'from host_ports1 t1 ' .
				'left outer join host_ports2 t2 on t1.host_id = t2.host_id '.
				'where t2.host_id is null';
		$host_results = $db->fetch_object_array($query);
	}
	if (is_array($host_results)) $content .= '<h4>' . count($host_results) . ' records found</h4>';
	//$content .= '<table id="table' . $port . '" class="tablesorter">';
	$content .= '<table id="table' . $port . '" class="port_report">';
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
	//}
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