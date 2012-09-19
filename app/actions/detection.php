<?php
/**
 * Show port detection from sensors
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2011.11.28
 */

require_once($approot . 'lib/class.Db.php');
$db = Db::get_instance();

// Port probes yesterday
/*
// Deprecated in favor of direct inserts to darknet table
$sql = 'SELECT rule_src_ip, ' .
	'SUBSTRING(a.rule_log, LOCATE("DPT=", a.rule_log), LOCATE(" WINDOW=", a.rule_log) - LOCATE("DPT=", a.rule_log)) AS portnumber ' .  
	'FROM ossec_alerts a, ossec_rules r ' .
	'WHERE a.alert_date > DATE_SUB(CURDATE(), INTERVAL 1 DAY) ' .
		'AND a.rule_src_ip != \'128.91.234.47\' ' .
		'AND a.host_id = 31 ' .
		'AND a.rule_id = r.rule_id ' .
		'AND r.rule_number = 104500 ' .
	'ORDER BY portnumber';*/
	
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
		'from ossec_alerts a, host h, ossec_rules r ' .
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