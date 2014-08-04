<?php
/** 
 * This is the report subcontroller
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Setup defaults.
 */
$title = 'Reports';
$file =  isset($_GET['report']) ? $approot . 'actions/' . basename($_GET['report']) . '.php' : 'icantexist';

$template = $templates . 'report.tpl.php';
if (isset($_GET['report'])&& file_exists($file)) {
		include_once($file);
}
if (! isset($_GET['ajax'])) include_once($templates. 'admin_headers.tpl.php');
include_once($template);
?>