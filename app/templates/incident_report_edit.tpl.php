<h1>Edit Incident Report</h1>
<form class="form-horizontal" method="post" name="<?php echo $ir_form_name;?>" id="<?php echo $ir_form_name;?>" action="?action=incident_report_edit_scr&id=<?php echo $id;?>">
<fieldset>
    <legend>Metadata</legend>
    <div class="control-group">
        <label class="control-label" for="incidentTitle">Title</label>
        <div class="controls">
            <input type="text" id="incidentTitle" name="incidentTitle" placeholder="Incident Title" class="input-xxlarge" value="<?php echo $report->get_title();?>">
        </div>
        <label class="control-label" for="incidentMonth">Month</label>
        <div class="controls">
            <select id="incidentMonth" name="incidentMonth" class="input-mini">
                <option value="1"<?php echo ($report->get_month() == 1) ? " selected" : "";?>>Jan</option>
                <option value="2"<?php echo ($report->get_month() == 2) ? " selected" : "";?>>Feb</option>
                <option value="3"<?php echo ($report->get_month() == 3) ? " selected" : "";?>>Mar</option>
                <option value="4"<?php echo ($report->get_month() == 4) ? " selected" : "";?>>Apr</option>
                <option value="5"<?php echo ($report->get_month() == 5) ? " selected" : "";?>>May</option>
                <option value="6"<?php echo ($report->get_month() == 6) ? " selected" : "";?>>Jun</option>
                <option value="7"<?php echo ($report->get_month() == 7) ? " selected" : "";?>>July</option>
                <option value="8"<?php echo ($report->get_month() == 8) ? " selected" : "";?>>Aug</option>
                <option value="9"<?php echo ($report->get_month() == 9) ? " selected" : "";?>>Sep</option>
                <option value="10"<?php echo ($report->get_month() == 10) ? " selected" : "";?>>Oct</option>
                <option value="11"<?php echo ($report->get_month() == 11) ? " selected" : "";?>>Nov</option>
                <option value="12"<?php echo ($report->get_month() == 12) ? " selected" : "";?>>Dec</option>
            </select>
            <select id="incidentYear" name="incidentYear" class="input-small">
            <?php
                for ($i=$cur_year;$i>$cur_year-10;$i--) {?>
                <option value="<?php echo $i;?>"<?php echo ($report->get_year() == $i) ? " selected" : "";?>><?php echo $i;?></option>
            <?php } ?>
            </select>
        </div>
        <label class="control-label" for="incidentTags">Tags</label>
		<div class="controls">
			<input type="text" id="incidentTags" name="incidentTags" placeholder="Incident Tags" class="input-xxlarge" value="<?php echo implode(', ',$report->get_tag_names());?>">
			</select><span class="help-inline tagTip"><i class="icon-info-sign"></i></span>
			<div id="availableTags" class="hidden"><?php echo $tags_json?></div>
		</div>
    </div>
    
    <legend>Details</legend>
    <label class="control-label" for="incidentAgent">Agent causing incident</label>
        <div class="controls">
            <select id="incidentAgent" name="incidentAgent">
                <?php foreach ($agents as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_agent_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline agentTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="incidentAction">Threat action</label>
        <div class="controls">
            <select id="incidentAction" name="incidentAction">
                <?php foreach ($actions as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_action_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline sourceTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="incidentAsset">Assets affected</label>
        <div class="controls">
            <select id="incidentAsset" name="incidentAsset">
                <?php foreach ($assets as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_asset_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select>
        </div>
        
    <legend>Loss</legend>
    <label class="control-label" for="incidentPII">Data exposure</label>
        <div class="controls">
            <label class="radio">
            <input type="radio" name="incidentPII" id="incidentPII"value="0"<?php echo (!$report->get_confidential_data() == $key) ? " checked" : "";?>>No confidential data
            </label>
            <label class="radio">
            <input type="radio" name="incidentPII" id="incidentPII" value="1"<?php echo ($report->get_confidential_data() == $key) ? " checked" : "";?>>Confidential data exposed
            </label>
        </div>
        
    <label class="control-label" for="integrityloss">Integrity loss</label>
        <div class="controls">
            <textarea name="integrityloss" rows="3" class="input-xxlarge"><?php echo $report->get_integrity_loss();?></textarea><span class="help-inline integrityLossTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="authenloss">Authenticity loss</label>
        <div class="controls">
            <textarea name="authenloss" rows="3" class="input-xxlarge"><?php echo $report->get_authenticity_loss();?></textarea><span class="help-inline authenticityLossTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="utilityloss">Utility loss</label>
        <div class="controls">
            <textarea name="utilityloss" rows="3" class="input-xxlarge"><?php echo $report->get_utility_loss();?></textarea><span class="help-inline utilityLossTip"><i class="icon-info-sign"></i></span>
        </div>
        
    <legend>Timeframes</legend>
    <label class="control-label" for="availabilityLoss">Availability loss</label>
        <div class="controls">
            <select id="availabilityLoss" name="availabilityLoss">
                <?php foreach ($timeframes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_availability_loss_timeframe_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline availabilityLossTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="incidentAtoD">Action to discovery</label>
        <div class="controls">
            <select id="incidentAtoD" name="incidentAtoD">
                <?php foreach ($timeframes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_action_to_discovery_timeframe_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline actionToDiscoveryTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="incidentDtoC">Discovery to containment</label>
        <div class="controls">
            <select id="incidentDtoC" name="incidentDtoC">
                <?php foreach ($timeframes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_discovery_to_containment_timeframe_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline discoveryToContainmentTip"><i class="icon-info-sign"></i></span>
        </div>
        
    <legend>Discovery</legend>
    <label class="control-label" for="incidentDisco">Method</label>
        <div class="controls">
            <select id="incidentDisco" name="incidentDisco">
                <?php foreach ($discoveries as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_discovery_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select>
        </div>
    <label class="control-label" for="evidencesources">Evidence sources</label>
        <div class="controls">
            <textarea name="evidencesources" rows="3" class="input-xxlarge"><?php echo $report->get_discovery_evidence_sources();?></textarea><span class="help-inline evidenceSourcesTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="othermetrics">Other metrics</label>
        <div class="controls">
            <textarea name="othermetrics" rows="3" class="input-xxlarge"><?php echo $report->get_discovery_metrics();?></textarea><span class="help-inline otherDiscoveryMetricsTip"><i class="icon-info-sign"></i></span>
        </div>
        
    <legend>Impact</legend>
    <label class="control-label" for="assetLossMag">Asset loss</label>
        <div class="controls">
            <select id="assetLossMag" name="assetLossMag">
                <?php foreach ($magnitudes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_asset_loss_magnitude_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline assetLossTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="disruptionMag">Business disruption</label>
        <div class="controls">
            <select id="disruptionMag" name="disruptionMag">
                <?php foreach ($magnitudes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_disruption_magnitude_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline businessDisruptionTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="responseCostMag">Response cost</label>
        <div class="controls">
            <select id="responseCostMag" name="responseCostMag">
                <?php foreach ($magnitudes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_response_cost_magnitude_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline responseCostTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="impactMag">Overall impact</label>
        <div class="controls">
            <select id="impactMag" name="impactMag">
                <?php foreach ($magnitudes as $key=>$val) {?>
                    <option value="<?php echo $key;?>"<?php echo ($report->get_impact_magnitude_id() == $key) ? " selected" : "";?>><?php echo $val; ?></option>
                <?php } ?>
            </select><span class="help-inline overallImpactTip"><i class="icon-info-sign"></i></span>
        </div>
        
    <legend>Remediation and Mitigation</legend>
    <label class="control-label" for="2020hindsight">20/20 hindsight solution</label>
        <div class="controls">
            <textarea name="2020hindsight" rows="3" class="input-xxlarge"><?php echo $report->get_hindsight();?></textarea><span class="help-inline 2020hindsightTip"><i class="icon-info-sign"></i></span>
        </div>
    <label class="control-label" for="correctiveaction">Corrective action recommended</label>
        <div class="controls">
            <textarea name="correctiveaction" rows="3" class="input-xxlarge"><?php echo $report->get_correction_recommended();?></textarea><span class="help-inline correctiveActionTip"><i class="icon-info-sign"></i></span>
        </div>
        
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save report</button>
    </div>
        
</fieldset>
<input type="hidden" name="token" value="<?php echo $ir_form_token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $ir_form_name;?>"/>
</form>