<?php
include_once($approot . 'templates/admin_headers.tpl.php');
require_once($approot . 'lib/class.Incident.php');

$incident = new Incident();
$incident->set_title($_POST['incidentTitle']);
$incident->set_month($_POST['incidentMonth']);

?>