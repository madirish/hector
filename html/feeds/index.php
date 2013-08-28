<?php
/**
 *  This is the export feed supercontroller
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Log/report access denied messages.
 */
 
/**
 * Setup the session for access
 */
session_name('HECTOR');
session_start();
// Global variables
$approot = getcwd() . '/../../app/';
$templates = $approot . 'templates/';

// Necessary includes 
require_once($approot . 'lib/class.Config.php');
new Config();

// Array of valid feeds
$actions = array('kojoney2_feed');


// Set up the action subcontroller or abort
if (isset($_GET['action']) && in_array($_GET['action'], $actions)) {
		$action = $_GET['action'];
}
else {
	die("Permission denied.");
}

/**
 * Hand off to subcontrollers
 */
include_once($approot . 'actions/' . $action . '.php');
 
?>
