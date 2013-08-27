<?php
/**
 * Subcontroller to gather details for a specific host.
 * 
 * @package HECTOR
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2013.08.28
 */
 
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Nmap_scan_result.php');
$db = Db::get_instance();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$host = new Host($id);
$tags = implode(',', $host->get_tag_names());
$sql = array('SELECT v.vuln_name, ' .
					'd.vuln_details_id, ' .
					'd.vuln_details_text, ' .
					'd.vuln_details_datetime, ' .
					'd.vuln_details_ignore, ' .
					'd.vuln_details_fixed ' .
				'FROM vuln_details d, ' .
					'vuln v, ' .
					'vuln_details_x_host x ' .
				'WHERE d.vuln_id = v.vuln_id AND ' .
					'd.vuln_details_id = x.vuln_details_id AND ' .
					'x.host_id = ?i ' .
				'ORDER BY d.vuln_details_datetime DESC', 
				$host->get_id());
$vulns = $db->fetch_object_array($sql);

$scans = new Collection('Nmap_scan_result', 
			' AND nsr.host_id = ' . $host->get_id(), 
			'', 
			' AND s.state_id = 1 ORDER BY nsr.nmap_scan_result_protocol, nsr.nmap_scan_result_port_number');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'host_details.tpl.php');
?>