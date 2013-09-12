<?php
/**
 * This is the subcontroller that handles login form
 * posts and validates credentials.
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 */
 
/**
 * Require User objects
 */
 require_once($approot . 'lib/class.User.php');

$user = new User();
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
if ($user->validate($username, $password)) {
	$_SESSION['user_id'] = $user->get_id();
	$action = 'summary';
	include_once($approot . 'actions/summary.php');
}
else {
	$form = new Form();
	$formname = 'login_form';
	$form->set_name($formname);
	$token = $form->get_token();
	$form->save();
	$action = 'login';
	$sorry = true;
	include_once($templates . 'header.tpl.php');
	include_once($approot . 'templates/login.tpl.php');
}

?>