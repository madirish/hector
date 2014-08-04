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
require_once($approot . 'lib/class.Vuln_detail.php');
$db = Db::get_instance();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$host = new Host($id);
$tags = implode(',', $host->get_tag_names());

$vulns = Vuln_detail::get_vuln_details_by_host($host->get_id(), $db);

$scans = new Collection('Nmap_result', 
			' AND nsr.host_id = ' . $host->get_id(), 
			'', 
			' AND s.state_id = 1 ORDER BY nsr.nmap_result_protocol, nsr.nmap_result_port_number');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'host_details.tpl.php');
?>