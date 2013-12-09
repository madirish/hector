
<dl class="dl-horizontal">
    <dt>Incident title</dt>
    <dd><?php echo $report->get_title();?></dd>
    <dt>Date</dt>
    <dd><?php echo $report->get_month_friendly() . ' ' . $report->get_year();?></dd>
    <dt>Agent</dt>
    <dd><?php echo $report->get_agent()->get_name();?></dd>
    <dt>Threat action</dt>
    <dd><?php echo $report->get_action()->get_action();?></dd>
    <dt>Assets affected</dt>
    <dd><?php echo $report->get_asset()->get_name();?></dd>
    <dt>Data exposed?</dt>
    <dd><?php echo $report->get_confidential_data() ? 'No confidential data exposed' : 'Yes, confidential data was exposed.';?></dd>
    <dt>Integrity loss</dt>
    <dd><?php echo $report->get_integrity_loss();?></dd>
    <dt>Authenticity loss</dt>
    <dd><?php echo $report->get_authenticity_loss();?></dd>
    <dt>Utility loss</dt>
    <dd><?php echo $report->get_utility_loss();?></dd>
    <dt>Availability loss</dt>
    <dd><?php echo $report->get_availability_loss_timeframe_friendly();?></dd>
    <dt>Action to discovery</dt>
    <dd><?php echo $report->get_action_to_discovery_timeframe_friendly();?></dd>
    <dt>Discovery to containment</dt>
    <dd><?php echo $report->get_discovery_to_containment_timeframe_friendly();?></dd>
    <dt>Discovery method</dt>
    <dd><?php echo $report->get_discovery_method_friendly();?></dd>
    <dt>Evidence sources</dt>
    <dd><?php echo $report->get_discovery_evidence_sources();?></dd>
    <dt>Other Metrics</dt>
    <dd><?php echo $report->get_discovery_metrics();?></dd>
    <dt>Asset loss</dt>
    <dd><?php echo $report->get_asset_loss_magnitude_friendly();?></dd>
    <dt>Business disruption</dt>
    <dd><?php echo $report->get_disruption_magnitude_friendly();?></dd>
    <dt>Response cost</dt>
    <dd><?php echo $report->get_response_cost_magnitude_friendly();?></dd>
    <dt>Overall impact</dt>
    <dd><?php echo $report->get_impact_magnitude_friendly();?></dd>
    <dt>20/20 hindsight</dt>
    <dd><?php echo $report->get_hindsight();?></dd>
    <dt>Corrections</dt>
    <dd><?php echo $report->get_correction_recommended();?></dd>
    <dt>&nbsp;</dt>
    <dd><a href="?action=incident_report_edit&id=<?php echo $id;?>" class="btn btn-primary">Edit</a></a>
</dl>