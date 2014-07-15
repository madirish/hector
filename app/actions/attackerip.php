<?php
/**
 * Show attacker ip's from darknet or ossec logs
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2011.11.28
 * @todo Move the SQL from this file into a utility class
 */
 
/**
 * Necessary includes
 */
require_once($approot . 'lib/class.Report.php');
require_once($approot . 'lib/class.Form.php');

/**
 * Get a form with XSRF protection
 */
$form = new Form();
$formname = 'search_attackerip_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();
	

$ip = '';
if (isset($_GET['ip'])) $ip = $_GET['ip']; 
if (isset($_POST['ip'])) $ip = $_POST['ip']; 

if (! filter_var($ip, FILTER_VALIDATE_IP)) $ip = '';

$darknet_drops = array();
$ossec_alerts = array();
if ($ip != '') {
	$report = new Report();
    $darknet_drops = $report->get_darknet_drops($ip);
    $login_attempts = $report->get_honeynet_logins($ip);
    $commands = $report->get_koj_executed_commands($ip);
	$ossec_alerts = $report->get_ossec_alerts($ip);
}

$ip_addr = ($ip == '') ? '' : htmlspecialchars($ip);
$ip_name = ($ip == '') ? '' : gethostbyaddr($ip);
$ip_rpt_display = $ip_addr;
if ($ip_addr != $ip_name) {
	$ip_rpt_display .= ' - ' . $ip_name;
}

$javascripts .= '<script type="text/javascript" charset="utf8" src="js/jquery.dataTables.js"></script>' . "\n";
$javascripts .= '<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">' . "\n";

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'attackerip.tpl.php');
	
?>