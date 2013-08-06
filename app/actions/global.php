<?php
/**
 *  controller to manage global variables
 *  by Josh Bauer <joshbauer3@gmail.com>
 */
require_once($approot . 'lib/class.Db.php');
global $appuser;

//badge that contains the number of vulnerabilities that are not ignored or fixed
global $vuln_badge;
if (! isset($appuser)) {
	if (! isset($_SESSION['user_id'])) die("<h2>Fatal error!<?h2>User not initialized.");
	else $appuser = new User($_SESSION['user_id']);
}
$sql = 'select count(vd.vuln_details_id) count from vuln_details vd ';
if (isset($appuser) && ! $appuser->get_is_admin()) {
			// Using this object via the web
			$sql .= 'inner join vuln_x_host vh on vh.vuln_details_id = vd.vuln_details_id ';
			$sql .= 'inner join host h on h.host_id = vh.host_id ';
			$sql .= 'inner join user_x_supportgroup us on us.supportgroup_id = h.supportgroup_id ';
			$sql = array($sql . 'where vd.vuln_details_ignore = 0 and vd.vuln_details_fixed = 0 and us.user_id = ?i', $appuser->get_id());
}
else {
			$sql .= 'where vd.vuln_details_ignore = 0 and vd.vuln_details_fixed = 0';
}
$db = Db::get_instance();
$result = $db->fetch_object_array($sql);
$vuln_count = $result[0]->count;
$vuln_badge = ($vuln_count > 0) ? '<span class="badge">' . $vuln_count . '</span>' : ''
?>