<?php
/**
 * Show honeypot data
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2012.11.27
 * @package HECTOR
 * @todo Move the SQL out of actions/honeypot.php and into a helper class
 */

/**
 * Necessary includes
 */
include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.HoneyPotConnect.php');


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

$username_frequencies = $hpconnect->get_field_frequencies($field='username',$bound);

$u_top = key($username_frequencies);
$u_frequency = $username_frequencies[$u_top];
$u_total = array_sum($username_frequencies);
$u_percent = round(($u_frequency / $u_total) * 100);

// Password frequencies
$password_frequencies = $hpconnect->get_field_frequencies($field='password',$bound);
$pass_top = key($password_frequencies);
$pass_frequency = $password_frequencies[$pass_top];
$pass_total = array_sum($password_frequencies);
$pass_percent = round(($pass_frequency / $pass_total) * 100);


// Country frequencies
$country_frequencies = $hpconnect->get_field_frequencies($field='country_code',$bound);
$c_top = key($country_frequencies);
$c_frequency = $country_frequencies[$c_top];
$c_total = array_sum($country_frequencies);
$c_percent = round(($c_frequency / $c_total) * 100);

// IP frequencies 
$ip_frequencies = $hpconnect->get_field_frequencies($field='ip',$bound);
$ip_top = key($ip_frequencies);
$ip_frequency = $ip_frequencies[$ip_top];
$ip_total = array_sum($ip_frequencies);
$ip_percent = round(($ip_frequency / $ip_total) * 100);

require_once($approot . 'lib/class.Form.php');
$form = new Form();
$formname = 'search_evilip_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

// Include CSS files;
$css = '';
$css .= "<link href='css/jquery.dataTables.css' rel='stylesheet'>\n";

// Include Javascripts;
$javascripts = '';
$javascripts .= "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/honeypot.js'></script>\n";


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'honeypot.tpl.php');

$db->close();
?>
