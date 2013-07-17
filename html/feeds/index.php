<?php
/**
 *  This is the feed supercontroller
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
session_name('HECTOR');
session_start();
// Global variables
$approot = getcwd() . '/../../app/';
$templates = $approot . 'templates/';

// Necessary includes 
require_once($approot . 'lib/class.Config.php');
new Config();

/**
 * Begin program flow control.  Build an array
 * of valid actions so we can hand off control 
 * from the GET variable without having to
 * worry about null byte injection.
 * Ref:  http://www.madirish.net/?article=436
 */
$actions = array();
if (! $files = opendir($approot . '/actions')) {
	die("Error opening actions directory.  Please contact a system administrator.");
}
while (($dir = readdir($files)) !== false) {
	if (substr($dir, -4) == ".php") $actions[] = substr($dir, 0, -4);
}

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
