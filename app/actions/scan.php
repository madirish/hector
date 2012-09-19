<?php

include_once($approot . 'lib/class.Collection.php');
include_once($approot . 'lib/class.Scan.php');
include_once($approot . 'lib/class.Scan_type.php');

$collection = new Collection('Scan');
$items = array();
if (isset($collection->members) && is_array($collection->members)) {
	foreach ($collection->members as $item) {
		$items[] = $item;
	}
}

if (! isset($_GET['ajax'])) include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'scan.tpl.php');

function translate_weekday($day) {
	$days =  array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
	return $days[$day];
}
?>