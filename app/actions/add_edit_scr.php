<?php
/**
* This is the generic subcontroller for modifying objects.
* The file expects a GET id element and a POST array that
* matches up to the object specified.
* 
* @package HECTOR
* @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
**/

$object_readable = 'Unknown';

if (! isset($_GET['object'])) {
	// in case we don't have the right input
	$template = 'default';
}
else {
	$object = ucfirst(urldecode($_GET['object']));	
	$file = $approot . 'lib/class.' . $object . '.php';
	if (is_file($file)) {
		/**
		 * Include the source file for the class we want to modify
		 */
		include_once($file);
		$id = isset($_GET['id']) ? intval($_GET['id']) : '';
		$generic = new $object($id);
		$post_values = $generic->get_add_alter_form();
		foreach ($post_values as $val) {
			//strip off any post array demarkers 
			if (substr($val['name'],-2)=='[]') $val['name'] = substr($val['name'], 0, -2);
			if (isset($_POST[$val['name']])) {// Empty checkboxes won't pass back
				// Single select items will submit as arrays with one value, flatten them
				if (is_array($_POST[$val['name']]) && count ($_POST[$val['name']]) == 1) {
					$_POST[$val['name']] = $_POST[$val['name']][0];
				}
				$generic->process_form($val['process_callback'], $_POST[$val['name']]);
			}
		}
		$generic->save();
		$object_readable = method_exists($generic, 'get_label') ? $generic->get_label() : str_ireplace("_"," ", $object);
	}
	$message = 'Record ';
	$message .=  ($id == '') ? 'created' : 'updated';
}
$updated = 'yes';



if (! isset($_GET['ajax'])) include_once($templates. 'admin_headers.tpl.php');
if (isset($template) && $template == 'default') {
	include_once($templates . 'default.tpl.php');
}
else {	
	$_GET['action'] = 'details';
    if (strtolower($object) == 'host')
        include_once($approot . 'actions/host_details.php');
    else
        include_once($approot . 'actions/details.php');
}
?>