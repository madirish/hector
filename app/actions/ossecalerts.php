<?php
/**
 * This is the default subcontroller for the 
 * OSSEC clients report
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @todo Move javascript inclusion into function
 */
 
/**
 * Necessary includes
 */
include_once($approot . 'lib/class.Collection.php');


$ipholder = '0.0.0.0';


$ossec_alert_collection = new Collection('Ossec_Alert','','get_ossec_alerts_in_last_week');
$ossec_alerts = array();
$ossec_timeline = array();
for ($i = 0; $i < 7; $i++ ){
	$day = date('l', strtotime("-$i day"));
	$ossec_timeline[$day] = 0;
}


if (is_array($ossec_alert_collection->members)){
	foreach ($ossec_alert_collection->members as $ossec_alert){
		$ossec_alerts[] = $ossec_alert->get_object_as_array();
		$day = date('l',strtotime($ossec_alert->get_alert_date()));
		$ossec_timeline[$day] += 1;	
	}
}

$ossec_timeline = array_reverse($ossec_timeline);
$timeline_keys = json_encode(array_keys($ossec_timeline));
$timeline_values = json_encode(array_values($ossec_timeline));
$record_count = count($ossec_alerts);

$ossec_rule_collection = new Collection('Ossec_Rule');
$ossec_rules = array();
if (is_array($ossec_rule_collection->members)){
	foreach($ossec_rule_collection->members as $rule){
		$ossec_rules[] = $rule->get_object_as_array();
		
	}
}

// css
$css = '';
$css .= "<link href='css/jquery.dataTables.css' rel='stylesheet'>\n";

// javascripts
$javascripts = '';
$javascripts .= "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/Chart.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/ossecalerts.js'></script>\n";


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'ossecalerts.tpl.php');