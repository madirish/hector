<?php
/**
 * This is the default subcontroller for the 
 * OSSEC clients report
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
require_once($approot . 'lib/class.Db.php');

$db = Db::get_instance();

$sql = 'SELECT a.*, r.rule_level, r.rule_message ' .
		'FROM ossec_alert a, ossec_rule r ' .
		'WHERE r.rule_id = a.rule_id ' .
		'ORDER BY a.alert_date ' .
		'LIMIT 100';
$alerts = $db->fetch_object_array($sql);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'ossecalerts.tpl.php');