<?php
/**
 * This is the default subcontroller for runs of a specific vulnerability scan
 * reports
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 */

/**
 * Require the factory class
 */
require_once($approot . 'lib/class.Vulnscan.php');
$scanid = isset($_GET['id']) ? $_GET['id'] : null;
$datetime = isset($_GET['datetime']) ? $_GET['datetime'] : null;
$vulnscan = null;
if (isset($_GET['id']) && isset($_GET['datetime'])) {
	$vulnscan = new Vulnscan($scanid, $datetime);
}

$hosts = array();
if (is_array($vulnscan->get_host_ids())) {
	foreach ($vulnscan->get_host_ids() as $host_id) {
		$vulns = Vuln_detail::get_vuln_details_by_host($host_id, $db, $scanid);
		$host = new Host($host_id);
		$urgents = 0;
		$criticals = 0;
		$seriouses = 0;
		$mediums = 0;
		$minimals = 0;
		foreach ($vulns as $vuln) {
			switch ($vuln->risk_id) {
				case 0: 
					$minimals++;
					break;
				case 1:
					$mediums++;
					break;
				case 2:
					$seriouses++;
					break;
				case 3:
					$criticals++;
					break;
				case 4:
					$urgents++;
					break;
			}
		}
		$hosts[] = array('host'=>new Host($host_id), 
							'vulns'=>vulns, 
							'minimals'=>$minimals, 
							'mediums'=>$mediums,
							'seriouses'=>$seriouses,
							'criticals'=>$criticals,
							'urgents'=>$urgents
		);
	}
}

include_once($templates . 'admin_headers.tpl.php');
include_once($templates . 'vulnscan_details.tpl.php');
?>