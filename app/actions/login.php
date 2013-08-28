<?php
/**
 * This is the controller for the login form.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 */
 
/**
 * Require XSRF protected forms.
 */
require_once($approot . 'lib/class.Form.php');

$form = new Form();
$formname = 'login_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

include_once($templates . 'login.tpl.php');

?>