<?php
include_once($approot . 'templates/admin_headers.tpl.php');
require_once($approot . 'lib/class.Incident.php');

$incident = new Incident();
$incident->set_title($_POST['incidentTitle']);
$incident->set_month($_POST['incidentMonth']);
$incident->set_year($_POST['incidentYear']);
$incident->set_agent_id($_POST['incidentAgent']);
$incident->set_action_id($_POST['incidentAction']);
$incident->set_asset_id($_POST['incidentAsset']);
$incident->set_confidential_data($_POST['incidentPII']);
$incident->set_integrity_loss($_POST['integrityloss']);
$incident->set_authenticity_loss($_POST['authenticityloss']);

?>