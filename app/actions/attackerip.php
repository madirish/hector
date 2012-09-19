<?php
/**
 * Show attacker ip's from darknet or ossec logs
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2011.11.28
 */

require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'search_attackerip_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();
	
include_once($templates. 'admin_headers.tpl.php');

$ip = '';
if (isset($_GET['ip'])) $ip = $_GET['ip']; 
if (isset($_POST['ip'])) $ip = $_POST['ip']; 

if ($ip != '') {
	require_once($approot . 'lib/class.Db.php');
	$db = Db::get_instance();

	$ip = mysql_real_escape_string($ip);
	$sql = 'select inet_ntoa(dst_ip) as dst_ip, src_port, dst_port, proto, received_at from darknet ' .
			'where src_ip = inet_aton(\'' . $ip . '\') order by received_at desc';
	$darknet_drops = $db->fetch_object_array($sql);
	
	$sql = 'select count(id) as thecount from koj_login_attempts where ip_numeric = inet_aton(\'' . $ip . '\')';
	$honeypot_logins = $db->fetch_object_array($sql);
	$login_attempts = $honeypot_logins[0]->thecount;
	if ($login_attempts == '') $login_attempts = 'no';
	
	$sql = 'select a.alert_date, a.rule_log, r.rule_level from ossec_alerts a, ossec_rules r ' .
			'where a.rule_id = r.rule_id and r.rule_level >= 7 AND ' .
			'a.rule_src_ip_numeric = inet_aton(\'' . $ip . '\') order by alert_date DESC';
	$ossec_alerts = $db->fetch_object_array($sql);
	
}

$tablename='darknet_drops';
	
$content .= '<table id="table' . $tablename . '" class="tablesorter">';
$content .= '<thead><tr><th>Attacker IP</th><th>Target IP</th><th>Source Port</th><th>Destination Port</th><th>Protocol</th><th>Observed at:</th></tr></thead><tbody>';
if (is_array($darknet_drops)) {
	foreach ($darknet_drops as $drop) {
		$content .= '<tr><td>' . $ip . '</td>';
		$content .= '<td>' . $drop->dst_ip . '</td>';
		$content .= '<td>' . $drop->src_port . '</td>';
		$content .= '<td>' . $drop->dst_port . '</td>';
		$content .= '<td>' . $drop->proto . '</td>';
		$content .= '<td>' . $drop->received_at . '</td>';
		$content .= '</tr>';
	}
}
$content .= '</tbody></table>';
$content .= '<script type="text/javascript">
$(document).ready(function() 
    { 
        $("#table' . $tablename . '").tablesorter(); 
    } 
); 
</script>';

include_once($templates . 'attackerip.tpl.php');
	
?>