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
$content .= '<h3>Web Servers</h3>' .
		'<h4>Excluding printers, Skype boxes, or ISUS managed machines</h4>';

$query = 'SELECT DISTINCT(h.host_id) FROM host h, nmap_scan_result nsr ' .
		'WHERE nsr.host_id = h.host_id AND ' .
		'h.supportgroup_id NOT IN (1,3) AND ' .
		'nsr.port_number IN (80,443) AND ' .
		'nsr.state_id = 1 AND ' .
		'h.host_os NOT LIKE \'%printer%\' AND ' .
		'LOWER(h.host_name) NOT LIKE \'%ricoh%\' AND ' .
		'LOWER(h.host_name) NOT LIKE \'%printer%\' AND ' .
		'LOWER(h.host_name) NOT LIKE \'%lexmark%\' AND ' .
		'LOWER(h.host_name) NOT LIKE \'%laserjet%\' AND ' .
		'LOWER(nsr.service_version) NOT LIKE \'%printer%\' AND ' .
		'nsr.service_version NOT LIKE \'%Virata-EmWeb%\' AND ' . 
		'nsr.service_version NOT LIKE \'%Virata-EmWeb%\' AND ' . 
		'nsr.service_version NOT LIKE \'%Agranat-EmWeb%\' AND ' . 
		'nsr.service_version NOT LIKE \'%Allegro RomPager%\' AND ' . 
		'nsr.service_version NOT LIKE \'%HP-ChaiSOE%\' AND ' . 
		'nsr.service_version NOT LIKE \'%ZOT-PS-19 print server%\' AND ' .
		'LOWER(nsr.service_version) NOT LIKE \'%skype%\' ' .
		'GROUP BY h.host_id';
$host_results = $db->fetch_object_array($query);
$content .= '<h3>Found: ' . count($host_results) . ' hosts</h3>';
$content .= '<table id="dhost"><tr>' .
		'<th>Host</th>' .
		'<th>IP</th>' .
		'<th>LSP Group</th></tr>' . "\n";
if (is_array($host_results)) {
	foreach ($host_results as $ret) {
		$host = new Host($ret->host_id);
		$supportgroup = new Supportgroup($host->get_supportgroup_id());
		$content .= '<tr><td>' . $host->get_name_linked() . '</td>' .
				'<td>' . $host->get_ip() . '</td>' .
				'<td style="white-space: nowrap;">' . $supportgroup->get_name() . '</td>' .
				'</tr>' . "\n";
	}	
}
$content .= '</table>';
?>