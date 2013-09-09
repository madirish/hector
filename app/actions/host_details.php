<?php
/**
 * Subcontroller to gather details for a specific host.
 * 
 * @package HECTOR
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @version 2013.08.28
 * @todo Move the SQL out of actions/host_details.php and into a helper class
 */

/**
 * Require the database
 */
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Nmap_result.php');
$db = Db::get_instance();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$host = new Host($id);
$tags = implode(',', $host->get_tag_names());
$sql = array('SELECT v.vuln_name, ' .
					'd.vuln_detail_id, ' .
					'd.vuln_detail_text, ' .
					'd.vuln_detail_datetime, ' .
					'd.vuln_detail_ignore, ' .
					'd.vuln_detail_fixed ' .
				'FROM vuln_detail d, ' .
					'vuln v ' .
				'WHERE d.vuln_id = v.vuln_id AND ' .
					'd.host_id = ?i ' .
				'ORDER BY d.vuln_detail_datetime DESC', 
				$host->get_id());
$vulns = $db->fetch_object_array($sql);

$scans = new Collection('Nmap_result', 
			' AND nsr.host_id = ' . $host->get_id(), 
			'', 
			' AND s.state_id = 1 ORDER BY nsr.nmap_result_protocol, nsr.nmap_result_port_number');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'host_details.tpl.php');
?>