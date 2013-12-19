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
					case 'date':
						$javascripts .= '<link href="css/datepicker.css" rel="stylesheet">';
						$javascripts .= '<script type="text/javascript" src="js/bootstrap-datepicker.js"></script>';
						$row['form'] = '';
						$row['form'] .= '<input type="text" class="span2" name="' . 
									$display['name'] . '" id="dp-' . $display['name'] . '" value="' . $value . '"/>';
						$row['form'] .= "\n\t\t";
						$footer_scripts .= '	<script type="text/javascript">$(function(){$(\'#dp-' . $display['name'] . '\').datepicker({format: \'yyyy-mm-dd\',todayBtn: \'linked\'});});</script>';
						$row['form'] .= "\n";
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
		$object_readable = str_ireplace("_"," ", $object);
		switch ($object) {
			case 'Scan':
				$object_readable = 'Scan Schedule';
				break;
			case 'Supportgroup':
				$object_readable = 'Support Group';
				break;
			case 'Vuln':
				$object_readable = 'Vulnerability Class';
				break;
			case 'Api_key':
				$object_readable = 'API Key';
				break;
			case 'IRAction':
				$object_readable = 'Incident Report Action';
				break;
			case 'IRAgent':
				$object_readable = 'Incident Report Agent';
				break;
			case 'IRAsset':
				$object_readable = 'Incident Report Asset';
				break;
			case 'IRDiscovery':
				$object_readable = 'Incident Report Discovery Method';
				break;
			case 'IRMagnitude':
				$object_readable = 'Incident Report Magnitude';
				break;
			case 'IRTimeframe':
				$object_readable = 'Incident Report Timeframe';
				break;
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