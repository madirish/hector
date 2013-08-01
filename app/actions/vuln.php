<?php
/**
 * This is the default subcontroller for vulnerability
 * reports
 * 
 *  by Josh Bauer <joshbauer3@gmail.com>
 * 
 */

include_once($templates . 'admin_headers.tpl.php');
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Vuln_details.php');
require_once($approot . 'lib/class.Vuln.php');
if (! isset($appuser)) {
	if (! isset($_SESSION['user_id'])) die("<h2>Fatal error!<?h2>User not initialized.");
	else $appuser = new User($_SESSION['user_id']);
}
$sql = 'SELECT vd.vuln_details_id, v.vuln_name, h.host_name,vh.host_id, vd.vuln_details_datetime, vd.vuln_details_fixed, vd.vuln_details_ignore ';
$sql .= 'FROM vuln_details vd inner join vuln v on v.vuln_id = vd.vuln_id ';
$sql .= 'inner join vuln_x_host vh on vd.vuln_details_id = vh.vuln_details_id ';
$sql .= 'inner join host h on vh.host_id = h.host_id';

if (isset($appuser) && ! $appuser->get_is_admin()) {
	$sql .= ' inner join user_x_supportgroup us on us.supportgroup_id = h.supportgroup_id ';
	$sql .= 'where us.user_id = ' . $appuser->get_id();
}

$vulns = $db->fetch_object_array($sql);
$db = Db::get_instance();
include_once($templates . 'vuln.tpl.php');
?>