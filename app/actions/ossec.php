<?php
/**
 * This is the default subcontroller
 */
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');

$db = Db::get_instance();


$sql = 'SELECT distinct(host_id) FROM ossec_alerts';
$result = $db->fetch_object_array($sql);
$hosts = array();
foreach($result as $host) {
	$hosts[] = new Host($host->host_id);
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'ossec.tpl.php');

?>