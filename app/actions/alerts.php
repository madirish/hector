<?php
/**
 * This is the default subcontroller for displaying system
 * alerts
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Include the Alert class
 */
require_once($approot . 'lib/class.Alert.php');

$db = Db::get_instance();
$limit = '';
$leveloptions = '';
$href = '?action=assets&object=alerts';
$startdateplaceholder = '0000-00-00';

if (isset($_POST['startdate']) && $_POST['startdate'] !== '') $_GET['startdate'] = $_POST['startdate'];
if (isset($_POST['enddate']) && $_POST['enddate'] !== '') $_GET['enddate'] = $_POST['enddate'];
if (isset($_POST['ip']) && $_POST['ip'] !== '') $_GET['ip'] = $_POST['ip'];

$startdate = (isset($_GET['startdate'])) ? date('Y-m-d', strtotime($_GET['startdate'])) : '';
if ($startdate !== '') {
	$limit .= ' AND a.alert_timestamp >= "' . $startdate . '" ';
	$href .= '&startdate=' . $startdate;
	$startdateplaceholder = $startdate;
}
$enddate = (isset($_GET['enddate'])) ? date('Y-m-d', strtotime($_GET['enddate'])) : date('Y-m-d');
if ($enddate !== date('Y-m-d')) {
	$limit .= ' AND a.alert_timestamp <= "' . $enddate . ' 23:59:59" ';
	$href .= '&enddate=' . $enddate;
}

$ip = '0.0.0.0';
if (isset($_GET['ip']) && filter_var($_GET['ip'], FILTER_VALIDATE_IP)) {
	$ip = $_GET['ip'];
	$limit .= ' AND h.host_ip_numeric = inet_aton("' . $ip . '") ';
	$href .= '&ip=' . $ip;
}


$sql = 'SELECT COUNT(a.alert_id) AS thecount ' .
		'FROM alert a, host h ' .
		'WHERE h.host_id = a.host_id ';
if ($limit !== '') {
	$sql .= $limit;
}

$results = $db->fetch_object_array($sql);
$thecount = $results[0]->thecount;

$startrecord = (isset($_GET['start'])) ? intval($_GET['start']) : 0;
$clearfilterurl = '?action=assets&object=alerts&start=' . $startrecord;
$curpage = floor($startrecord/50);
$nextstart = $startrecord + 50;
$prevstart = $startrecord - 50;

$pager = "";
if ($prevstart > 0) {
	$pager .= "\t";
	$pager .= '<li><a href="' . $href . '&start=' . $prevstart . '">Prev</a></li>';
	$pager .= "\n";
}
else {
	$pager .= "\t";
	$pager .= '<li class="disabled"><a href="#">Prev</a></li>';
	$pager .= "\n";
}


// limit 10
$maxloop = $startrecord + 250;
$minloop = $startrecord - 250;
if ($minloop < 0) {
	$minloop = 0;
	$maxloop = 500;
}
if ($maxloop > $thecount) {
	$maxloop = $thecount - ($thecount%50); // so 820 records gets maxloop 800
	$minloop = (($maxloop - 500) > 0) ? $maxloop - 500 : 0;
}

if ($minloop > 0) {
	$pager .= "\t";
	$pager .= '<li class="disabled"><a href="#">...</a></li>';
	$pager .= "\n";
}
// put the counter in the middle
for ($i = $minloop + 1; $i <= $maxloop + 1; $i = $i + 50) {
	$disabled = ($i == $startrecord+1) ? ' class="active"' : '';
	$pager .= "\t";
	$pager .= '<li' . $disabled . '>';
	$pager .= '<a href="' . $href . '&start=' . ($i - 1) . '">' . (floor($i/50) + 1) . '</a>';
	$pager .= '</li>';
	$pager .= "\n";
}
if (($maxloop + 50) < $thecount) {
	$pager .= "\t";
	$pager .= '<li class="disabled"><a href="#">...</a></li>';
	$pager .= "\n";
}
if ($nextstart < $thecount) {
	$pager .= "\t";
	$pager .= '<li><a href="' . $href . '&start=' . $nextstart . '">Next</a></li>';
	$pager .= "\n";
}


$sql = 'SELECT a.alert_id ' .
		'FROM alert a, host h ' .
		'WHERE a.host_id = h.host_id ';
if ($limit !== '') {
	$sql .= $limit;
}
$sql .= 'ORDER BY a.alert_timestamp desc ' .
		'LIMIT ' . $startrecord . ',50 '; 
		
$alerts = $db->fetch_object_array($sql);

// Necessary includes for filter form
require_once($approot . 'actions/global.php');
require_once($approot . 'lib/class.Form.php');
$filter_form = new Form();
$filter_form->set_name('alert_filter_form');
$filter_form_token = $filter_form->get_token();
$filter_form->save();

$alertsin = '';
foreach($alerts as $alert) {
	$alertsin .= $alert->alert_id . ',';
}
$alertsin = substr($alertsin, 0, -1);
$collection = new Collection('Alert', 
	' AND alert_id IN (' . $alertsin . ')');
$alerts = $collection->members;


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'alerts.tpl.php');

?>