<?php
/**
 * This is the default subcontroller.
 * The default is to show a list of hosts.
 * 
 * This page also diverges from the normal use of 
 * Host objects as creating a collection of 254 hosts
 * takes quite a long time.  The direct SQL method
 * is much faster to render.
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.28
 */

require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Collection.php');
$db = Db::get_instance();

if (isset($_GET['classB'])) {
	$query = 'select distinct(substring_index(host_ip, \'.\', 3)) as ipclass, count(host_id) as thecount';
	$query .= ' from host where host_ip like \'?s%\' group by ipclass';
	$query = array($query, $_GET['classB']);
	$range = htmlspecialchars($_GET['classB']);
}
elseif (isset($_GET['classC'])) {
	$hosts = array();
	$classC = mysql_real_escape_string($_GET['classC']);
	// Buildin a Collection is too heavy so we'll just SQL it
	$sql = array(
		'SELECT h.host_id, h.host_name, h.host_ip, h.host_os, COUNT(n.nmap_scan_result_id) AS portcount ' .
		'FROM host h ' .
		'LEFT OUTER JOIN nmap_scan_result n ' .
		'ON n.host_id = h.host_id AND n.state_id = 1 ' .
		'WHERE h.host_ip LIKE \'?s%\' GROUP BY h.host_id, h.host_name ORDER BY h.host_ip_numeric',
		$classC
	);	
	$hosts = $db->fetch_object_array($sql);
}
else {
	$query = 'select distinct(substring_index(host_ip, \'.\', 2)) as ipclass, count(host_id) as thecount';
	$query .= ' from host group by ipclass';
}

if (isset($query)) $ip_ranges = $db->fetch_object_array($query); 

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'browse_ip.tpl.php');

?>