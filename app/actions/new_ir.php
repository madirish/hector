<?php 
include_once($approot . 'templates/admin_headers.tpl.php');
require_once($approot . 'lib/class.Form.php');
require_once($approot . 'lib/class.IRAgent.php');
require_once($approot . 'lib/class.IRAsset.php');
require_once($approot . 'lib/class.IRDiscovery.php');
$ir_form = new Form();
$ir_form_name = 'incident_report_form';
$ir_form->set_name($ir_form_name);
$ir_form_token = $ir_form->get_token();
$ir_form->save();

$cur_year = date("Y");

$iragents = new Collection("IRAgent");
$agents = array();
if (is_array($iragents->members)) {
	foreach ($iragents->members as $agent) {
		$agents[$agent->get_id()] = $agent->get_name();
	}
}

$iractions = new Collection("IRAction");
$actions = array();
if (is_array($iractions->members)) {
	foreach ($iractions->members as $action) {
		$actions[$action->get_id()] = $action->get_action();
	}
}

$irassets = new Collection("IRAsset");
$assets = array();
if (is_array($irassets->members)) {
	foreach ($irassets->members as $asset) {
		$assets[$asset->get_id()] = $asset->get_name();
	}
}

$irtimeframes = new Collection("IRTimeframe");
$timeframes = array();
if (is_array($irtimeframes->members)) {
	foreach ($irtimeframes->members as $timeframe) {
		$timeframes[$timeframe->get_id()] = $timeframe->get_duration();
	}
}

$irdiscoveries = new Collection("IRDiscovery");
$discoveries = array();
if (is_array($irdiscoveries->members)) {
	foreach ($irdiscoveries->members as $discovery) {
		$discoveries[$discovery->get_id()] = $discovery->get_method();
	}
}

include_once($approot . 'templates/new_ir.tpl.php');
?>