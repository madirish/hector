<?php
/**
 * This is the default subcontroller for vulnerability
 * management
 */
include_once($templates. 'admin_headers.tpl.php');
require_once($approot . 'lib/class.Vuln_details.php');
require_once($approot . 'lib/class.Vuln.php');
if (isset($_GET['id']) && ($_GET['id'] != '')) {
	$vuln_details= new Vuln_details(intval($_GET['id']));
	$vuln = new Vuln($vuln_details->get_vuln_id());
	include_once($templates . 'vuln_details.tpl.php');
}
?>