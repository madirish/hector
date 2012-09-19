<?php
/**
 * This is the default subcontroller.
 * The default is to show a list of hosts.
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2011.02.22
 */

require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Collection.php');


if (isset($_GET['classB'])) {
	$query = 'select distinct(substring_index(host_ip, \'.\', 3)) as ipclass, count(host_id) as thecount';
	$query .= ' from host where host_ip like \'?s%\' group by ipclass';
	$query = array($query, $_GET['classB']);
	$range = htmlspecialchars($_GET['classB']);
}
elseif (isset($_GET['classC'])) {
	$hosts = array();
	$classC = mysql_real_escape_string($_GET['classC']);
	$collection = new Collection('Host', ' AND host_ip like \'' . $classC . '%\'', '', ' order by host_ip');
	if (isset($collection->members) && is_array($collection->members)) {
		foreach ($collection->members as $item) {
			$hosts[] = $item;
		}
	}
}
else {
	$query = 'select distinct(substring_index(host_ip, \'.\', 2)) as ipclass, count(host_id) as thecount';
	$query .= ' from host group by ipclass';
}

$db = Db::get_instance();

if (isset($query)) $result = $db->fetch_object_array($query);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'default.tpl.php');

?>