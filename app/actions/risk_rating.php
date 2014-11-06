<?php
/**
 * Vulnerability details by risk
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
require_once($approot . 'lib/class.Risk.php');
require_once($approot . 'lib/class.Vuln_detail.php');

$risk_id = isset($_GET['risk_id']) ? $_GET['risk_id'] : 0;
$vuln_details = array();
$risk = new Risk($risk_id);

$details = $risk->get_vuln_detail_ids(); 
if (is_array($details)) {
	foreach ($details as $id) {
		$vuln_details[] = new Vuln_detail($id);
	}
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'risk_rating.tpl.php');

?>