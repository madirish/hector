<?php
/**
 * Show Website Screenshots ordered by host_id
 * @author: Ubani Anthony Balogun <ubani@sas.upenn.edu>
 * @package: Hector
 * 
 */

require_once($approot . 'lib/class.Db.php');
include_once($approot . 'lib/class.Collection.php');

// screenshots.css
$css = '';
$css .= "<link href='css/screenshots.css' rel='stylesheet'>\n";


$javascripts .= "<script type='text/javascript' src='js/screenshots.js'></script>\n";

$screenshot_collection = new Collection('Url');
$screenshots = array();

if (is_array($screenshot_collection->members)){
	foreach ($screenshot_collection->members as $screenshot){
		if ($screenshot->get_screenshot() != null){
			$host_id = $screenshot->get_host_id();
			if (isset($screenshots[$host_id]['screenshot'])){
				$screenshots[$host_id]['screenshot'] .= $screenshot->get_screenshot_link();
			}else{
				$screenshots[$host_id]['screenshot'] = $screenshot->get_screenshot_link();
				$screenshots[$host_id]['name'] = $screenshot->get_host_name();	
			}
		}
	}
	
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates. 'screenshots.tpl.php');

?>