<?php
/**
 * Show honeypot data
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2012.11.27
 * @package HECTOR
 */

/**
 * Necessary includes
 */
include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.HoneyPotConnect.php');
include_once($approot . 'lib/class.HoneyPotSession.php');

//Honey Pot Login Attempts

$bound = ' AND time > DATE_SUB(NOW(), INTERVAL 7 DAY)';

if (isset($_GET['country'])){
	$country = substr($_GET['country'], 0, 2);
	$country = strtoupper($country);
	$country = mysql_real_escape_string($country);
	$bound .= " AND country_code = '$country'";
}
$honey_pot = new Collection('HoneyPotConnect', $filter = $bound);
$attempts = array();

if (is_array($honey_pot->members)){
	foreach ($honey_pot->members as $attempt){
		$attempts[] = $attempt->get_object_as_array();
	}
}
$attempts_json = json_encode($attempts);


// Honey Pot Sessions
$honeypotsession = new Collection('HoneyPotSession', $filter = $bound);
$commands = array();

if (is_array($honeypotsession->members)){
	foreach ($honeypotsession->members as $command){
		$commands[] = $command->get_object_as_array();
	}
}
$commands_json = json_encode($commands);


//  Username frequencies
$hpconnect = new HoneyPotConnect();

$username_top = $hpconnect->get_top_field_percent($field='username',$bound=7);
$u_top = (!empty($username_top)) ? key($username_top) : '';
$u_percent = (isset($username_top[$u_top])) ? $username_top[$u_top] : 0;

$pass_top_percent = $hpconnect->get_top_field_percent($field='password',$bound=7);
$pass_top = (!empty($pass_top_percent)) ? key($pass_top_percent) : '';
$pass_percent = (isset($pass_top_percent[$pass_top])) ? $pass_top_percent[$pass_top] : 0;

$country_code_top = $hpconnect->get_top_field_percent($field='country_code',$bound=7);
$c_top = (!empty($country_code_top)) ? key($country_code_top) : '';
$c_percent = (isset($country_code_top[$c_top])) ? $country_code_top[$c_top] : 0;

$ip_top_percent = $hpconnect->get_top_field_percent($field='ip',$bound=7);
$ip_top = (!empty($ip_top_percent)) ? key($ip_top_percent) : '';
$ip_percent = (isset($ip_top_percent[$ip_top])) ? $ip_top_percent[$ip_top] : 0;

$hpsession = new HoneyPotSession();
// IP frequencies
$sip_top_percent = $hpsession->get_top_field_percent($field='ip',$bound=7);
$sip_top = (!empty($sip_top_percent)) ? key($sip_top_percent) : '';
$sip_percent = (isset($sip_top_percent[$sip_top])) ? $sip_top_percent[$sip_top] : 0;


// Country frequencies
$scount_top_percent = $hpsession->get_top_field_percent($field='country_code',$bound=7);
$scount_top = (!empty($scount_top_percent)) ? key($scount_top_percent) : '';
$scount_percent = (isset($scount_top_percent[$scount_top])) ? $scount_top_percent[$scount_top] : 0; 

// Command frequencies
$command_freqs = $hpsession->get_field_frequencies($field='command',$bound=7);
if (!empty($command_freqs)){
	$top_command_keys = array_slice(array_keys($command_freqs),0,9);
	$top_command_vals = array_slice(array_values($command_freqs),0,9);
	$labels = json_encode($top_command_keys);
	$data = json_encode($top_command_vals);
}else{
	$labels =  $data = '[]';
}


require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'search_evilip_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

// Include JS files;
hector_add_js('honeypot.js');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'honeypot.tpl.php');

$db->close();
?>
