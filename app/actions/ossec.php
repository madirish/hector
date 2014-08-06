<?php
/**
 * This is the default subcontroller to show OSSEC alerts
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Move the SQL out of this file and into a class
 */

/**
 * Require the database
 */
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');

$db = Db::get_instance();


$sql = 'SELECT distinct(host_id) FROM ossec_alert';
$result = $db->fetch_object_array($sql);
$hosts = array();
foreach($result as $host) {
	$host_object = new Host($host->host_id);
	$hosts[] = $host_object->get_object_as_array();
	//$hosts[] = new Host($host->host_id);
}
$hosts_json = json_encode($hosts);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'ossec.tpl.php');

?>