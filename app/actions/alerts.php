<?php
/**
 * This is the default subcontroller
 */
require_once($approot . 'lib/class.Alert.php');

$filter = ' ORDER BY alert_timestamp DESC LIMIT 60';
$collection = new Collection('Alert', ' AND alert_string LIKE \'%to open%\'', '', $filter);
$alerts = $collection->members;

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'alerts.tpl.php');

?>