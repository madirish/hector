<?php
/**
 * Show honeypot data
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2012.11.27
 */

require_once($approot . 'lib/class.Db.php');
$db = Db::get_instance();

// Latest auth attempts
$sql = 'select ip, time, username, password from koj_login_attempts ' .
		'order by time desc limit 30;';
$login_attempts = $db->fetch_object_array($sql);
array_map(htmlspecialchars, $login_attempts);

// Get the latest sessions:
$sql = 'select time, ip, command from koj_executed_commands ' .
		'where time > date_sub(curdate(), interval 2 day) order by ip, time asc';
$commands = $db->fetch_object_array($sql);

require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'search_evilip_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'honeypot.tpl.php');

$db->close();
?>