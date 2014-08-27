<?php
/** 
 * This is the report subcontroller for searches
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Move HTML out of actions/search.php and into a template
 */

/**
 * Require the XSRF safe form
 */
require_once($approot . 'lib/class.Form.php');

$order = ' ORDER BY h.host_name';

$hosts = array();
$show_results = FALSE;
    
if (isset($_POST['hostname']) && $_POST['hostname'] != '') {
    require_once($approot . 'lib/class.Collection.php');
    $hostname = mysql_real_escape_string($_POST['hostname']);
    $collection = new Collection('Host', ' AND h.host_name LIKE \'%'.$hostname.'%\'', '', $order);

    foreach ($collection->members as $member) $hosts[] = $member;
    $show_results = TRUE;
}
if (isset($_POST['ip']) && $_POST['ip'] != '') {
    require_once($approot . 'lib/class.Collection.php');
    $ip = mysql_real_escape_string($_POST['ip']);
    $collection = new Collection('Host', ' AND h.host_ip LIKE \'%'.$ip.'%\'', '', $order);

    foreach ($collection->members as $member) $hosts[] = $member;
    $show_results = TRUE;
}
if (isset($_POST['version']) && $_POST['version'] != '') {
    require_once($approot . 'lib/class.Collection.php');
    $version = mysql_real_escape_string($_POST['version']);
    $collection = new Collection('Host', $version, 'get_collection_by_version', $order);

    foreach ($collection->members as $member) $hosts[] = $member;
    $show_results = TRUE;
}


$form = new Form();
$formname = 'search_form';
$form->set_name($formname);
$token = $form->get_token();
$form->save();


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'search_form.tpl.php');

if ($show_results) {
	include_once($templates . 'search_results.tpl.php');
}

?>