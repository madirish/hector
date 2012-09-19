<?php
/**
 * @todo Create form token to protect against XSRF
 */
 
require_once($approot . 'lib/class.Form.php');

$form = new Form();
$formname = 'login_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

include_once($templates . 'login.tpl.php');

?>