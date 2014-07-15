<?php
/**
 * Process the edits to an incident report.
 * 
 * @package HECTOR
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 */
include_once($approot . 'templates/admin_headers.tpl.php');
require_once($approot . 'lib/class.Incident.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($incident = new Incident($id)) {
    $incident->set_title($_POST['incidentTitle']);
    $incident->set_month($_POST['incidentMonth']);
    $incident->set_year($_POST['incidentYear']);
    $incident->set_agent_id($_POST['incidentAgent']);
    $incident->set_action_id($_POST['incidentAction']);
    $incident->set_asset_id($_POST['incidentAsset']);
    $incident->set_confidential_data($_POST['incidentPII']);
    $incident->set_integrity_loss($_POST['integrityloss']);
    $incident->set_authenticity_loss($_POST['authenloss']);
    $incident->set_utility_loss($_POST['utilityloss']);
    $incident->set_availability_loss_timeframe_id($_POST['availabilityLoss']);
    $incident->set_action_to_discovery_timeframe_id($_POST['incidentAtoD']);
    $incident->set_discovery_to_containment_timeframe_id($_POST['incidentDtoC']);
    $incident->set_discovery_id($_POST['incidentDisco']);
    $incident->set_discovery_evidence_sources($_POST['evidencesources']);
    $incident->set_discovery_metrics($_POST['othermetrics']);
    $incident->set_asset_loss_magnitude_id($_POST['assetLossMag']);
    $incident->set_disruption_magnitude_id($_POST['disruptionMag']);
    $incident->set_response_cost_magnitude_id($_POST['responseCostMag']);
    $incident->set_impact_magnitude_id($_POST['impactMag']);
    $incident->set_hindsight($_POST['2020hindsight']);
    $incident->set_correction_recommended($_POST['correctiveaction']);
    
    if (! $incident->save()) {
        die("Error: Unable to save incident report edits.");
    }
    ?>
<script type="text/javascript">location.href='?action=incident_report_summary&id=<?php echo $incident->get_id();?>';</script>
    <?php
}
?>