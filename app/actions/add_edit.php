<?php
/**
 * This is the generic subcontroller for adding new objects.
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */

/**
 * Include the Form in order to have XSRF protection
 */
require_once($approot . 'lib/class.Form.php');
$add_edit = 1;
if (! isset($_GET['object'])) {
	// in case we don't have the right input
	$template = 'default';
}
else { 
	$object = ucfirst(urldecode($_GET['object']));	
	$file = $approot . 'lib/class.' . $object . '.php';
	if (is_file($file)) {
		include_once($file);
		$id = isset($_GET['id']) ? intval($_GET['id']) : '';
		$generic = new $object($id);
		// Include any object specific JavaScripts if necessary
		if (method_exists($generic,'get_footer_scripts')) $footer_scripts .= $generic->get_footer_scripts();
		
		// Work out the display
		$form_data = array();
		$displays = $generic->get_add_alter_form();
		if (is_array($displays)) {
			foreach ($displays as $display) {
				$row = array();
				$row['label'] = $display['label'];
				$value = (isset($display['value_function'])) ? call_user_func(array($generic, $display['value_function'])) : '';
				switch ($display['type']) {
					case 'textarea':
						$row['form'] = '<textarea type="text" name="' . 
										$display['name'] . '">' . $value . '</textarea>';
						break;
					case 'text':
						$row['form'] = '<input type="text" name="' . 
										$display['name'] . '" id="' . 
										$display['name'] . '" value="' . $value . '" class="text"/>';
						break;
					case 'password':
						$row['form'] = '<input type="password" name="' . 
										$display['name'] . '" id="' . 
										$display['name'] . '" value="' . $value . '"/>';
						break;
					case 'select':
						$row['form'] = '<select name="' . $display['name'] . '" id="' . 
										$display['name'] . '">';
						foreach ($display['options'] as $key=>$val) {
							$row['form'] .= '<option value="' . $key . '"';
							if ($key == $value) {
								$row['form'] .= ' selected="selected"';
							}
							if (isset($display['onselects'])) {
								$row['form'] .= ' onClick="javascript:' . $display['onselects'][$key] . '" ';
							}
							$row['form'] .= ' id="' . 
										$key . '">' . $val . '</option>';
						}
						$row['form'] .= '</select>'; 
						break;
					case 'hidden':
						$row['form'] = '<input type="hidden" name="' . 
										$display['name'] . '" id="' . 
										$display['name'] . '" value="' . $value . '"/>';
						break;
					case 'checkbox':
						$row['form'] = '';
						foreach ($display['options'] as $key=>$val) {
							$row['form'] .= "\t" . '<input type="checkbox" name="' . $display['name'] . '"  value="' . $key . '"';
							if (is_array($value) && in_array($key,$value)) $row['form'] .= ' checked="checked"';
							$row['form'] .= '/>' . $val . '<br/>' . "\n";
						}
						break;
					default:
						$row['form'] = '';
				}
				$form_data[] = $row;
			}
		}
		$form_name = ($id=='') ? 'add_' . strtolower($object) . '_form' : 'edit_' . strtolower($object) . '_form';
		
		// Find necessary templates, if unique
		switch($object) {
			case 'User': 
				$template = 'add_edit_user';
				break;
			case 'Scan_type':
				$template = 'add_edit_scan_type';
				break;
			default:
				$template = 'add_edit';
		}
		
		$form = new Form();
		$form->set_name($form_name);
		$token = $form->get_token();
		$form->save();
	}
}

if (! isset($_GET['ajax'])) include_once($templates. 'admin_headers.tpl.php');
include_once($templates . $template . '.tpl.php');
?>