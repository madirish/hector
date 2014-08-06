<?php
/**
 * Edit an incident report.
 * 
 * @package HECTOR
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 */
 

require_once($approot . 'lib/class.Form.php');
include_once($approot . 'lib/class.Incident.php');

// Javascripts
$javascripts = '';
$javascripts .= "<script type='text/javascript' src='js/jquery-ui.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/new_ir.js'></script>\n";

// CSS
$css = '';
$css .= "<link href='css/jquery-ui.min.css' rel='stylesheet'>\n";

$ir_form = new Form();
$ir_form_name = 'incident_report_edit_form';
$ir_form->set_name($ir_form_name);
$ir_form_token = $ir_form->get_token();
$ir_form->save();

$cur_year = date("Y");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$report = new Incident($id);

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

$irmagnitudes = new Collection("IRMagnitude");
$magnitudes = array();
if (is_array($irmagnitudes->members)) {
    foreach ($irmagnitudes->members as $magnitude) {
        $magnitudes[$magnitude->get_id()] = $magnitude->get_name();
    }
}

$irtags = new Collection("Tag");
$tags = array();
if (is_array($irtags->members)){
	foreach ($irtags->members as $tag){
		$tags[] = $tag->get_name();
	}
}

$tags_json = json_encode($tags);

include_once($approot . 'templates/admin_headers.tpl.php');
include_once($approot . 'templates/incident_report_edit.tpl.php');
?>