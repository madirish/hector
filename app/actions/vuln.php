<?php
/**
 * This is the default subcontroller for vulnerability
 * reports
 * 
 *  by Josh Bauer <joshbauer3@gmail.com>
 * 
 */

require_once($approot . 'lib/class.Db.php');

$sql = 'SELECT * from (';
$sql .= 'SELECT vd.vuln_detail_id, vd. vuln_detail_text, v.vuln_name, h.host_name,vh.host_id, vd.vuln_detail_datetime, vd.vuln_detail_fixed, vd.vuln_detail_ignore ';
$sql .= 'FROM vuln_detail vd inner join vuln v on v.vuln_id = vd.vuln_id ';
$sql .= 'inner join vuln_detail_x_host vh on vd.vuln_detail_id = vh.vuln_detail_id ';
$sql .= 'inner join host h on vh.host_id = h.host_id';



if (isset($appuser) && ! $appuser->get_is_admin()) {
	$sql .= ' inner join user_x_supportgroup us on us.supportgroup_id = h.supportgroup_id ';
	$sql = array(
		$sql . 'where us.user_id = ?i order by vd.vuln_detail_datetime desc' . 
		') as temp_table group by temp_table.vuln_detail_text,temp_table.host_id order by vuln_detail_datetime desc' ,
		 $appuser->get_id());
}
else $sql .= ' order by vd.vuln_detail_datetime desc) as temp_table group by temp_table.vuln_detail_text,temp_table.host_id order by vuln_detail_datetime desc';
$db = Db::get_instance();
$vulns = $db->fetch_object_array($sql);


include_once($templates . 'admin_headers.tpl.php');
include_once($templates . 'vuln.tpl.php');
?>