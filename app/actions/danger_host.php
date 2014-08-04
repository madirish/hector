<?php
/**
 * Report on dangerous hosts, that is, hosts with four or 
 * more common server ports open
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Remove SQL into helper objects
 */

/**
 * Setup defaults.
 */
$content = '';
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Supportgroup.php');
$db = Db::get_instance();
$content .= '<h3>Dangerous Hosts</h3>';
$has_content = false;

$query = 'select n.host_id, h.supportgroup_id ' .
		'from nmap_result n, host h ' .
		'where n.host_id=h.host_id AND n.state_id=1 ' .
		'group by n.host_id having count(n.nmap_result_port_number) > 7 ' .
		'order by h.supportgroup_id';
$host_results = $db->fetch_object_array($query);

if (is_array($host_results) && count($host_results) > 0) {
	$content .= '<h4>Hosts with more than 7 open ports</h4>';
	$content .= '<table id="dhost"><tr>' .
		'<th>Host</th>' .
		'<th>IP</th>' .
		'<th>Support Group</th>' .
		'<th>Open Ports</th></tr>' . "\n";
		
	foreach ($host_results as $ret) {
		$host = new Host($ret->host_id);
		$supportgroup = new Supportgroup($ret->supportgroup_id);
		$ports = array();
		foreach($host->get_ports() as $port) {
			if ($port->get_state() == 'open') $ports[] = $port->get_port_number();
		}
		$ports = implode(', ', $ports);
		$content .= '<tr><td>' . $host->get_name_linked() . '</td>' .
				'<td>' . $host->get_ip() . '</td>' .
				'<td style="white-space: nowrap;">' . $supportgroup->get_name() . '</td>' .
				'<td>' . $ports . '</td></tr>' . "\n";
	}	
	$content .= '</table>';
	$has_content = true;
}

$query = 'select n.host_id, h.supportgroup_id ' .
		'from nmap_result n, host h ' .
		'WHERE n.host_id=h.host_id ' .
		'AND n.state_id=1 ' .
		'AND n.nmap_result_port_number IN (21,22,23,25,53,80,110,143,443,993,1433,1521,3306,8080) ' .
		'group by n.host_id having count(n.nmap_result_port_number) > 4 ' .
		'order by h.supportgroup_id;';
$host_results = $db->fetch_object_array($query);

if (is_array($host_results) && count($host_results)) {
	$content .= '<h4>Hosts with more than 4 <a href="#dangerModal" data-toggle="modal" title="About server ports">server</a> ports open:</h4>';
	$content .= <<<EOT
<div id="dangerModal" class="modal hide fade" role="dialog" aria-labelledby="dangerModal" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	<h3 id="dangerLabel">Server Ports</h3>
	</div>
	<div class="modal-body">
	<p>Server ports are defined as port 21 (ftp), 22 (ssh), 23 (telnet), 25 (smtp), 53 (DNS), 80 (http), 110 (POP3), 143 (IMAP), 443 (https), 993 (IMAPS), 1433 (MS-SQL), 1521 (Oracle SQL), 3306 (MySQL), and 8080 (http).</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>
EOT;
	$content .= '<table id="dhost"><tr>' .
		'<th>Host</th>' .
		'<th>IP</th>' .
		'<th>Support Group</th>' .
		'<th>Open Ports</th></tr>' . "\n";
	foreach ($host_results as $ret) {
		$host = new Host($ret->host_id);
		$supportgroup = new Supportgroup($ret->supportgroup_id);
		$isprinter = (in_array(1, $host->get_tag_ids())) ? '  (printer)' : '';
		$ports = array();
		foreach($host->get_ports() as $port) {
			if ($port->get_state() == 'open') $ports[] = $port->get_port_number();
		}
		$ports = implode(', ', $ports);
		$content .= '<tr><td>' . $host->get_name_linked() . $isprinter . '</td><td>' . 
			$host->get_ip() . '</td>' .
			'<td style="white-space: nowrap;">' . $supportgroup->get_name() . '</td>' . 
			'<td>' . $ports . '</tr>' . "\n";
	}	
	$content .= '</table>';
	$has_content = true;
}
if (! $has_content) {
	$content .= <<<EOT
	
No <a href="#dangerModal" data-toggle="modal" title="About dangerous hosts">dangerous hosts</a> detected by port scans.
<div id="dangerModal" class="modal hide fade" role="dialog" aria-labelledby="dangerModal" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	<h3 id="dangerLabel">Dangerous Hosts</h3>
	</div>
	<div class="modal-body">
	<p>Dangerous hosts are hosts with more than four ports open as detected by an NMAP scan.</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>
	
EOT;
}
$template = $templates . 'dangerhost.tpl.php';

?>