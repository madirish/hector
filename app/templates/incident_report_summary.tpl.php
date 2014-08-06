<h1><?php echo $report->get_title();?></h1>
    <legend>Metadata</legend>
    <p><?php echo $report->get_title();?></p>
    <p><?php echo $report->get_month_friendly() . ' ' . $report->get_year();?></p>
    <p><?php if (is_array($report->get_tag_ids())) echo "Tags: " . implode(", ",$report->get_tag_names());?></p>
    
    <legend>Details</legend>
    <div class="row">
        <div class="span2">Agent</div>
        <div class="span4"><?php echo $report->get_agent()->get_name();?></div>
    </div>
    <div class="row">
        <div class="span2">Threat action</div>
        <div class="span4"><?php echo $report->get_action()->get_action();?></div>
    </div>
    <div class="row">
        <div class="span2">Assets affected</div>
        <div class="span4"><?php echo $report->get_asset()->get_name();?></div>
    </div>
        
    <legend>Loss</legend>
    <div class="row">
        <div class="span2">Data exposure</div>
        <div class="span4"><?php echo $report->get_confidential_data() ? 'Yes, confidential data was exposed.' : 'No confidential data exposed';?></div>
    </div>
    <div class="row">
        <div class="span2">Integrity loss</div>
        <div class="span4"><?php echo $report->get_integrity_loss();?></div>
    </div>
    <div class="row">
        <div class="span2">Authenticity loss</div>
        <div class="span4"><?php echo $report->get_authenticity_loss();?></div>
    </div>
    <div class="row">
        <div class="span2">Utility loss</div>
        <div class="span4"><?php echo $report->get_utility_loss();?></div>
    </div>
        
    <legend>Timeframes</legend>
    <div class="row">
        <div class="span2">Availability loss</div>
        <div class="span4"><?php echo $report->get_availability_loss_timeframe_friendly();?></div>
    </div>
    <div class="row">
        <div class="span2">Action to discovery</div>
        <div class="span4"><?php echo $report->get_action_to_discovery_timeframe_friendly();?></div>
    </div>
    <div class="row">
        <div class="span2">Discovery to containment</div>
        <div class="span4"><?php echo $report->get_discovery_to_containment_timeframe_friendly();?></div>
    </div>
        
    <legend>Discovery</legend>
    <div class="row">
        <div class="span2">Method</div>
        <div class="span4"><?php echo $report->get_discovery_method_friendly();?></div>
    </div>
    <div class="row">
        <div class="span2">Evidence sources</div>
        <div class="span4"><?php echo $report->get_discovery_evidence_sources();?></div>
    </div>
    </div>
    <div class="row">
        <div class="span2">Other metrics</div>
        <div class="span4"><?php echo $report->get_discovery_metrics();?></div>
    </div>
        
    <legend>Impact</legend>
    <div class="row">
        <div class="span2">Asset loss</div>
        <div class="span4"><?php echo $report->get_asset_loss_magnitude_friendly();?></div>
    </div>
    <div class="row">
        <div class="span2">Business disruption</div>
        <div class="span4"><?php echo $report->get_disruption_magnitude_friendly();?></div>
    </div>
    <div class="row">
        <div class="span2">Response cost</div>
        <div class="span4"><?php echo $report->get_response_cost_magnitude_friendly();?></div>
    </div>
    <div class="row">
        <div class="span2">Overall impact</div>
        <div class="span4"><?php echo $report->get_impact_magnitude_friendly();?></div>
    </div>
        
    <legend>Remediation and Mitigation</legend>
    <div class="row">
        <div class="span2">20/20 hindsight</div>
        <div class="span4"><?php echo $report->get_hindsight();?></div>
    </div>
    <div class="row">
        <div class="span2">Corrections</div>
        <div class="span4"><?php echo $report->get_correction_recommended();?></div>
    </div>
        
<a href="?action=incident_report_edit&id=<?php echo $id;?>" class="btn btn-primary">Edit</a>