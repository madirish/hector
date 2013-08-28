<?php
/**
 * This is the form preprocessor, its main function is to
 * guard against XSRF by checking forms for valid tokens.
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Necessary includes
 */
require_once($approot . 'lib/class.Form.php');

$form = new Form();

if (! (isset($_POST['form_name']) || isset($_POST['token'])) ) {
	$form->log->write_error('XSRF attack detected!');
	print "Possible XSRF detected!";
	die();
}

if (! $form->validate($_POST['form_name'], $_POST['token'], $_SERVER['REMOTE_ADDR'])) {
	$form->log->write_error('Possible XSRF attack, form did not validate.');
	// Wipe out all the values
	foreach ($_POST as $key=>$val) {
		$_POST[$key] = '';
	}
}
?>