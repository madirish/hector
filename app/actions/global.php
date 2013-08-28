<?php
/**
 * Utility page to manage global variables for the interface
 *  
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.28
 */

/**
 * Necessary includes
 */
require_once($approot . 'lib/class.Db.php');
global $appuser;
$db = Db::get_instance();

//badge that contains the number of vulnerabilities that are not ignored or fixed
$vuln_badge = '';

if (! isset($appuser)) {
	if (! isset($_SESSION['user_id'])) die("<h2>Fatal error!<?h2>User not initialized.");
	else $appuser = new User($_SESSION['user_id']);
}
$sql = 'SELECT COUNT(d.vuln_detail_id) AS vulncount ' .
		'FROM vuln_detail d, host h ' .
		'WHERE ' .
			'h.host_id = d.host_id AND ' .
			'd.vuln_detail_ignore = 0 AND ' .
			'd.vuln_detail_fixed = 0';
if (isset($appuser) && ! $appuser->get_is_admin()) {
			// Limit reports to hosts the user is responsible for
			$sql .= ' AND d.host_id = h.host_id ' .
					' AND h.supportgroup_id IN (' . implode(',', $appuser->get_supportgroup_ids()) . ')';
}

$result = $db->fetch_object_array($sql);
if (isset($result[0])) {
	$vuln_count = $result[0]->vulncount;
	$vuln_badge = ($vuln_count > 0) ? '<span class="badge badge-important">' . $vuln_count . '</span>' : '';
}

$reports = ($vuln_count > 0) ? 'Reports <span class="badge badge-important">!</span>' : 'Reports';
?>