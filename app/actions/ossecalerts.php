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

// css
$css = '';
$css .= "<link href='css/jquery.dataTables.css' rel='stylesheet'>\n";

// javascripts
$javascripts = '';
$javascripts .= "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/ossecalerts.js'></script>\n";

$ipholder = '0.0.0.0';


$ossec_alert_collection = new Collection('Ossec_Alert','','get_ossec_alerts_in_last_week');
$ossec_alerts = array();

if (is_array($ossec_alert_collection->members)){
	foreach ($ossec_alert_collection->members as $ossec_alert){
		$ossec_alerts[] = $ossec_alert->get_object_as_array();
	}
}

$record_count = count($ossec_alerts);


$ossec_rule_collection = new Collection('Ossec_Rule');
$ossec_rules = array();
if (is_array($ossec_rule_collection->members)){
	foreach($ossec_rule_collection->members as $rule){
		$ossec_rules[] = $rule->get_object_as_array();
		
	}
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'ossecalerts.tpl.php');