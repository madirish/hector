<?php
/**
 * This is the generic subcontroller.  It is in fact a factory
 * That produces the detailed view of the specified object.
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Setup defaults.
 */
if (! isset($_GET['object'])) {
	// in case we don't have the right input
	$template = 'default';
}
else {
	$object = htmlspecialchars(ucfirst(urldecode($_GET['object'])));
	$title = $object . " Details";
	$file = $approot . 'lib/class.' . $object . '.php';
	if (isset($_GET['id']) && ($_GET['id'] != '')) {
		// generate a unique detail 
		if (is_file($file)) {
			include_once($file);
			$specific = new $object(intval($_GET['id']));
			
			// Work out the display
			$output = 'An error occurred. Cannot retrieve details for specific object (perhaps no get_details() method exists).';
			if (method_exists($specific, 'get_details')) {
				$output = $specific->get_details();
				$template = 'unique';
			}
			else {
				// Try the overview route
				require_once($approot . 'lib/class.Collection.php');
				$generic = new $object;
				$collection = new Collection($object);
				$items = array();
				if (isset($collection->members) && is_array($collection->members)) {
					foreach ($collection->members as $item) {
						$items[] = $item;
					}
				}
				
				// Work out the display
				$displays = $generic->get_displays();
				$template = 'details';
			}
		}
	}
	else {
		// generate an overview
		if (is_file($file)) {
			include_once($file);
			require_once($approot . 'lib/class.Collection.php');
			$generic = new $object;
			$collection = new Collection($object);
			$items = array();
			if (isset($collection->members) && is_array($collection->members)) {
				foreach ($collection->members as $item) {
					$items[] = $item;
				}
			}
			
			// Work out the display
			$displays = $generic->get_displays();
			$template = 'details';
		}
	}
}

if (! isset($_GET['ajax']) && ! isset($ajax)) {
	include_once($templates. 'admin_headers.tpl.php');
}
include_once($templates . $template . '.tpl.php');
?>