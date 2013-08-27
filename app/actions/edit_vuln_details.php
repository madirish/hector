<?php
/**
 * This is the default subcontroller for editing vulnerability
 * details
 * 
 *  by Josh Bauer <joshbauer3@gmail.com>
 * 
 */
include_once($templates. 'admin_headers.tpl.php');
require_once($approot . 'lib/class.Vuln_detail.php');
if (isset($_GET['id']) && ($_GET['id'] != '')) {
	$vuln_details= new Vuln_details(intval($_GET['id']));
	
	if (isset($_POST['submit']) && ($_POST['submit'] = 'Save changes')) {
		//echo implode(' : ', array_keys($_POST)) . ' | ';
		//echo implode(' : ', $_POST);
		if (isset($_POST['text'])) $vuln_details->set_text($_POST['text']);
		
		if (isset($_POST['ignore']) && $_POST['ignore']== 'on') $vuln_details->set_ignore(1);
		else $vuln_details->set_ignore(0);
		
		if (isset($_POST['fixed']) && $_POST['fixed']== 'on')  $vuln_details->set_fixed(1);
		else $vuln_details->set_fixed(0);
		
		if (isset($_POST['fixed_date'])) $vuln_details->set_fixed_datetime($_POST['fixed_date']);
		
		if (isset($_POST['fixed_notes'])) $vuln_details->set_fixed_notes($_POST['fixed_notes']);
		
		$vuln_details->save();
		echo 'Record updated';
		$vuln_details= new Vuln_details(intval($_GET['id']));
		include_once($templates . 'vuln_details.tpl.php');
	}
	else
	{
		$form_name='edit_vuln_details';
		$form = new Form();
		$form->set_name($form_name);
		$token = $form->get_token();
		$form->save();
		include_once($templates . 'edit_vuln_details.tpl.php');
	}
}
?>