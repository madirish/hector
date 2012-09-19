<?php
/**
 * Report on dangerous hosts, that is, hosts with four or 
 * more common server ports open
 */
$content = '';
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Lspgroup.php');
$db = Db::get_instance();
$content .= '<h3>Dangerous Hosts</h3>' .
		'<h4>Hosts with more than 7 open ports</h4>';

$query = 'select n.host_id, h.lspgroup_id ' .
		'from nmap_scan_result n, host h ' .
		'where n.host_id=h.host_id AND n.state_id=1 ' .
		'group by n.host_id having count(n.port_number) > 7 ' .
		'order by h.lspgroup_id';
$host_results = $db->fetch_object_array($query);
$content .= '<table id="dhost"><tr>' .
		'<th>Host</th>' .
		'<th>IP</th>' .
		'<th>LSP Group</th>' .
		'<th>Open Ports</th></tr>' . "\n";
if (is_array($host_results)) {
	foreach ($host_results as $ret) {
		$host = new Host($ret->host_id);
		$lspgroup = new Lspgroup($ret->lspgroup_id);
		$ports = array();
		foreach($host->get_ports() as $port) {
			if ($port->get_state() == 'open') $ports[] = $port->get_port_number();
		}
		$ports = implode(', ', $ports);
		$content .= '<tr><td>' . $host->get_name_linked() . '</td>' .
				'<td>' . $host->get_ip() . '</td>' .
				'<td style="white-space: nowrap;">' . $lspgroup->get_name() . '</td>' .
				'<td>' . $ports . '</td></tr>' . "\n";
	}	
}
$content .= '</table>';

$content .= '<h4>Hosts with more than 4 "server" ports open:</h4>';
$query = 'select n.host_id, h.lspgroup_id ' .
		'from nmap_scan_result n, host h ' .
		'WHERE n.host_id=h.host_id ' .
		'AND n.state_id=1 ' .
		'AND n.port_number IN (21,22,23,25,53,80,100,110,143,443,3306,8080) ' .
		'group by n.host_id having count(n.port_number) > 4 ' .
		'order by h.lspgroup_id;';
$host_results = $db->fetch_object_array($query);
$content .= '<table id="dhost"><tr>' .
		'<th>Host</th>' .
		'<th>IP</th>' .
		'<th>LSP Group</th>' .
		'<th>Open Ports</th></tr>' . "\n";
if (is_array($host_results)) {
	foreach ($host_results as $ret) {
		$host = new Host($ret->host_id);
		$lspgroup = new Lspgroup($ret->lspgroup_id);
		$isprinter = (in_array(1, $host->get_tag_ids())) ? '  (printer)' : '';
		$ports = array();
		foreach($host->get_ports() as $port) {
			if ($port->get_state() == 'open') $ports[] = $port->get_port_number();
		}
		$ports = implode(', ', $ports);
		$content .= '<tr><td>' . $host->get_name_linked() . $printer . '</td><td>' . 
			$host->get_ip() . '</td>' .
			'<td style="white-space: nowrap;">' . $lspgroup->get_name() . '</td>' . 
			'<td>' . $ports . '</tr>' . "\n";
	}	
}
$content .= '</table>';


?>