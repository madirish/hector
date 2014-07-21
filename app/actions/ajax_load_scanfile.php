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
        /**
         * Include the specified form page for which we'll load the processor
         */
		include_once($fpath);
	}
}