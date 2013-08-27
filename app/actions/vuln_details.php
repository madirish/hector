<?php
/**
 * This is the default subcontroller for vulnerability
 * details
 * 
 * by Josh Bauer <joshbauer3@gmail.com>
 * 
 */
include_once($templates. 'admin_headers.tpl.php');
require_once($approot . 'lib/class.Vuln_detail.php');
if (isset($_GET['id']) && ($_GET['id'] != '')) {
	$vuln_details= new Vuln_detail(intval($_GET['id']));
	include_once($templates . 'vuln_details.tpl.php');
}
?>