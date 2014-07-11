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
require_once($approot . 'lib/class.Db.php');
include_once($approot . 'lib/class.Collection.php');

// Include CSS files;
$css = '';
$css .= "<link href='css/jquery.dataTables.css' rel='stylesheet'>\n";
$css .= "<link href='css/dataTables.bootstrap.css' rel='stylesheet'>\n";

// Include Javascripts;
$javascripts = '';
$javascripts .= "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/honeypotlogins.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/dataTables.bootstrap.js'></script>\n";

$honey_pot = new Collection('HoneyPotConnect');
$attempts = array();

if (is_array($honey_pot->members)){
	foreach ($honey_pot->members as $attempt){
		$attempts[] = $attempt->get_object_as_array();
	}
}
$attempts_json = json_encode($attempts);

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