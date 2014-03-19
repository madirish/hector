<?php
/**
* This is the generic subcontroller for deleting objects.
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Setup defaults.
 */
 
$object_readable = 'Unknown';

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
		if ($id != '') {
			$target = new $object($id);
			$target->delete();
		}
	}
	$message = 'Record deleted';
	$_GET['id'] = null;
	$object_readable = method_exists($target, 'get_label') ? $target->get_label() : str_ireplace("_"," ", $object);
}
if (isset($_GET['ajax'])) {
	$ajax = true;
}
if (isset($template) && $template == 'default') {
	include_once($templates . $template . '.tpl.php');
}
else {
	$_GET['action'] = 'details';
	include_once($approot . 'actions/details.php');
}
?>