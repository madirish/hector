<?php
/**
 * Show port detection from sensors
 * 
 * @package HECTOR
 * @version 2013.08.29
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @todo Move the SQL out of this file into helper classes.
 */

/**
 * Require the database
 */
require_once($approot . 'lib/class.Db.php');
$db = Db::get_instance();



$javascripts .= "<script type='text/javascript' src='js/detection.js'></script>\n";
	
// Query ports probed on the darknet
$sql = 'select count(id) as cid, dst_port, proto from darknet ' .
	'where received_at > date_sub(curdate(), interval 1 day) ' .
	'group by dst_port order by cid desc';
$port_result = $db->fetch_object_array($sql);

// Query attacker IP's detected by the darknet sensors
$sql = 'select distinct(inet_ntoa(src_ip)) as evilip from darknet ' .
	'where received_at > date_sub(curdate(), interval 1 day) ' .
	'order by id desc limit 20';
$darknet_result = $db->fetch_object_array($sql);

// Get attackers detected by OSSEC in last 24 hours
$sql = 'select distinct(a.rule_src_ip) as evilip ' .
		'from ossec_alert a, host h, ossec_rule r ' .
		'where a.rule_id = r.rule_id AND r.rule_level >= 7 ' .
		'AND h.host_ip_numeric != a.rule_src_ip_numeric ' .
		'AND a.host_id = h.host_id and a.alert_date > date_sub(curdate(), interval 2 day) ' .
		'order by a.alert_date limit 30';
$ossec_attackers = $db->fetch_object_array($sql);

require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'search_evilip_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'detection.tpl.php');

$db->close();
?>