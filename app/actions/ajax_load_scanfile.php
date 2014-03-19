<?php
/**
 * Load files over AJAX to support scans
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
 
if (isset($_GET['scan'])) {
	$fpath = $approot . 'scripts/' .  basename($_GET['scan']) . '/form.php';
	if (file_exists($fpath)) {
		include_once($fpath);
	}
}