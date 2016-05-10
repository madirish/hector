<?php
/**
 * This is the default subcontroller for vulnerability scans overview
 * reports
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 */

/**
 * Require the factory class
 */
require_once($approot . 'lib/class.Report.php');
$report = new Report();
$vulnscans = $report->get_vulnscans();

include_once($templates . 'admin_headers.tpl.php');
include_once($templates . 'vulnscans.tpl.php');
?>