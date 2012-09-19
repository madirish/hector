<?php
/**
 * Report on dangerous hosts, that is, hosts with four or 
 * more common server ports open
 */
$content = '';
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Supportgroup.php');
$db = Db::get_instance();
$content .= '<h3>Web servers</h3>' .
		'<h4>Hosts with port 80 or 443 open excluding Skype and machines tagged as printers.</h4>';

$query = 'select n.host_id, h.supportgroup_id ' .
		'from nmap_scan_result n, host h ' .
		'where n.host_id = h.host_id and n.state_id=1 ' .
		'and n.port_number in (80,443) ' .
		'AND LOWER(n.service_version) NOT LIKE \'%skype%\' ' .
		'AND h.host_id NOT IN (SELECT host_id from host_x_tag) ' .
		'order by h.supportgroup_id';
$host_results = $db->fetch_object_array($query);
$content .= '<table id="dhost"><tr>' .
		'<th>Host</th>' .
		'<th>IP</th>' .
		'<th>LSP Group</th>' .
		'<th>Open Ports</th></tr>' . "\n";
$x=1;
if (is_array($host_results)) {
	foreach ($host_results as $ret) {
		$host = new Host($ret->host_id);
		$supportgroup = new Supportgroup($ret->supportgroup_id);
		$ports = array();
		foreach($host->get_ports() as $port) {
			if ($port->get_state() == 'open') $ports[] = $port->get_port_number();
		}
		$ports = implode(', ', $ports);
		$content .= '<tr><td>' . $x . '. '. $host->get_name_linked() . '</td>' .
				'<td>' . $host->get_ip() . '</td>' .
				'<td style="white-space: nowrap;">' . $supportgroup->get_name() . '</td>' .
				'<td>' . $ports . '</td></tr>' . "\n";
		$x++;
	}	
}
$content .= '</table>';
?>