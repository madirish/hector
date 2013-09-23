<?php
/**
 * This is the default subcontroller for editing vulnerability
 * details
 * 
 *  
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @package HECTOR
 */

/**
 * Necessary includes
 */
require_once($approot . 'lib/class.Vuln_detail.php');

if (isset($_GET['id']) && ($_GET['id'] != '')) {
	$vuln_detail= new Vuln_detail(intval($_GET['id']));
	
	if (isset($_POST['submit']) && ($_POST['submit'] = 'Save changes')) {
		//echo implode(' : ', array_keys($_POST)) . ' | ';
		//echo implode(' : ', $_POST);
		if (isset($_POST['text'])) $vuln_detail->set_text($_POST['text']);
		
		if (isset($_POST['ignore']) && $_POST['ignore']== 'on') {
			$vuln_detail->set_ignore(1);
			if (!$vuln_detail->get_ignored_user_id()>0)$vuln_detail->set_ignore_user_id($appuser->get_id());		
		}
		else 
		{
			$vuln_detail->set_ignore(0);
			$vuln_detail->set_ignore_user_id(0);
		}
		
		if (isset($_POST['fixed']) && $_POST['fixed']== 'on') {
			$vuln_detail->set_fixed(1);
			if (!$vuln_detail->get_fixed_user_id()>0) $vuln_detail->set_fixed_user_id($appuser->get_id());
		}
		else {
			$vuln_detail->set_fixed(0);
			$vuln_detail->set_fixed_user_id(0);
		}
		if (isset($_POST['fixed_date'])) $vuln_detail->set_fixed_datetime($_POST['fixed_date']);
		
		if (isset($_POST['fixed_notes'])) $vuln_detail->set_fixed_notes($_POST['fixed_notes']);

		$vuln_detail->save();
		// echo 'Record updated';
		$vuln_detail= new Vuln_detail(intval($_GET['id']));
		$edit_vuln_template = $templates . 'vuln_details.tpl.php';
	}
	else
	{
		$form_name='edit_vuln_details';
		$form = new Form();
		$form->set_name($form_name);
		$token = $form->get_token();
		$form->save();
		$edit_vuln_template = $templates . 'edit_vuln_details.tpl.php';
	}
}
include_once($templates. 'admin_headers.tpl.php');
include_once($edit_vuln_template);
?>