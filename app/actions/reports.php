<?php
/** 
 * This is the report subcontroller
 */

$title = 'Reports';
$file =  isset($_GET['report']) ? $approot . 'actions/' . basename($_GET['report']) . '.php' : 'icantexist';

if (isset($_GET['report'])&& file_exists($file)) {
		include_once($file);
}
if (! isset($_GET['ajax'])) include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'report.tpl.php');
?>