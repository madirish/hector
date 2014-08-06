<h1>New Incident Report</h1>
<form class="form-horizontal" method="post" name="<?php echo $ir_form_name;?>" id="<?php echo $ir_form_name;?>" action="?action=new_ir_scr">
<fieldset>
	<legend>Metadata</legend>
	<div class="control-group">
		<label class="control-label" for="incidentTitle">Title</label>
		<div class="controls">
			<input type="text" id="incidentTitle" name="incidentTitle" placeholder="Incident Title" class="input-xxlarge">
		</div>
		<label class="control-label" for="incidentMonth">Month</label>
		<div class="controls">
			<select id="incidentMonth" name="incidentMonth" class="input-mini">
				<option value="1">Jan</option>
				<option value="2">Feb</option>
				<option value="3">Mar</option>
				<option value="4">Apr</option>
				<option value="5">May</option>
				<option value="6">Jun</option>
				<option value="7">July</option>
				<option value="8">Aug</option>
				<option value="9">Sep</option>
				<option value="10">Oct</option>
				<option value="11">Nov</option>
				<option value="12">Dec</option>
			</select>
			<select id="incidentYear" name="incidentYear" class="input-small">
			<?php
				for ($i=$cur_year;$i>$cur_year-10;$i--) {?>
				<option value="<?php echo $i;?>"><?php echo $i;?></option>
			<?php } ?>
			</select>
		</div>
		<label class="control-label" for="incidentTags">Tags</label>
		<div class="controls">
			<input type="text" id="incidentTags" name="incidentTags" placeholder="Tags relevant to Incident" class="input-xxlarge">
			<div id="availableTags" class="hidden"><?php echo $tags_json?></div>
		</div>
	
	</div>
	
	<legend>Details</legend>
	<label class="control-label" for="incidentAgent">Agent causing incident</label>
		<div class="controls">
			<select id="incidentAgent" name="incidentAgent">
				<?php foreach ($agents as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline agentTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="incidentAction">Threat action</label>
		<div class="controls">
			<select id="incidentAction" name="incidentAction">
				<?php foreach ($actions as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline sourceTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="incidentAsset">Assets affected</label>
		<div class="controls">
			<select id="incidentAsset" name="incidentAsset">
				<?php foreach ($assets as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
	
		
	<legend>Loss</legend>
	<label class="control-label" for="incidentPII">Data exposure</label>
		<div class="controls">
			<label class="radio">
			<input type="radio" name="incidentPII" id="incidentPII" value="0" checked>No confidential data
			</label>
			<label class="radio">
			<input type="radio" name="incidentPII" id="incidentPII" value="1">Confidential data exposed
			</label>
		</div>
		
	<label class="control-label" for="integrityloss">Integrity loss</label>
		<div class="controls">
			<textarea name="integrityloss" rows="3" class="input-xxlarge"></textarea><span class="help-inline integrityLossTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="authenloss">Authenticity loss</label>
		<div class="controls">
			<textarea name="authenloss" rows="3" class="input-xxlarge"></textarea><span class="help-inline authenticityLossTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="utilityloss">Utility loss</label>
		<div class="controls">
			<textarea name="utilityloss" rows="3" class="input-xxlarge"></textarea><span class="help-inline utilityLossTip"><i class="icon-info-sign"></i></span>
		</div>
		
	<legend>Timeframes</legend>
	<label class="control-label" for="availabilityLoss">Availability loss</label>
		<div class="controls">
			<select id="availabilityLoss" name="availabilityLoss">
				<?php foreach ($timeframes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline availabilityLossTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="incidentAtoD">Action to discovery</label>
		<div class="controls">
			<select id="incidentAtoD" name="incidentAtoD">
				<?php foreach ($timeframes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline actionToDiscoveryTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="incidentDtoC">Discovery to containment</label>
		<div class="controls">
			<select id="incidentDtoC" name="incidentDtoC">
				<?php foreach ($timeframes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline discoveryToContainmentTip"><i class="icon-info-sign"></i></span>
		</div>
		
	<legend>Discovery</legend>
	<label class="control-label" for="incidentDisco">Method</label>
		<div class="controls">
			<select id="incidentDisco" name="incidentDisco">
				<?php foreach ($discoveries as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
	<label class="control-label" for="evidencesources">Evidence sources</label>
		<div class="controls">
			<textarea name="evidencesources" rows="3" class="input-xxlarge"></textarea><span class="help-inline evidenceSourcesTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="othermetrics">Other metrics</label>
		<div class="controls">
			<textarea name="othermetrics" rows="3" class="input-xxlarge"></textarea><span class="help-inline otherDiscoveryMetricsTip"><i class="icon-info-sign"></i></span>
		</div>
		
	<legend>Impact</legend>
	<label class="control-label" for="assetLossMag">Asset loss</label>
		<div class="controls">
			<select id="assetLossMag" name="assetLossMag">
				<?php foreach ($magnitudes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline assetLossTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="disruptionMag">Business disruption</label>
		<div class="controls">
			<select id="disruptionMag" name="disruptionMag">
				<?php foreach ($magnitudes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline businessDisruptionTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="responseCostMag">Response cost</label>
		<div class="controls">
			<select id="responseCostMag" name="responseCostMag">
				<?php foreach ($magnitudes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline responseCostTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="impactMag">Overall impact</label>
		<div class="controls">
			<select id="impactMag" name="impactMag">
				<?php foreach ($magnitudes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select><span class="help-inline overallImpactTip"><i class="icon-info-sign"></i></span>
		</div>
		
	<legend>Remediation and Mitigation</legend>
	<label class="control-label" for="2020hindsight">20/20 hindsight solution</label>
		<div class="controls">
			<textarea name="2020hindsight" rows="3" class="input-xxlarge"></textarea><span class="help-inline 2020hindsightTip"><i class="icon-info-sign"></i></span>
		</div>
	<label class="control-label" for="correctiveaction">Corrective action recommended</label>
		<div class="controls">
			<textarea name="correctiveaction" rows="3" class="input-xxlarge"></textarea><span class="help-inline correctiveActionTip"><i class="icon-info-sign"></i></span>
		</div>
		
	<div class="form-actions">
		<button type="submit" class="btn btn-primary">Save report</button>
    </div>
		
</fieldset>
<input type="hidden" id="selectedTags" name="selectedTags" value="">
<input type="hidden" name="token" value="<?php echo $ir_form_token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $ir_form_name;?>"/>
</form>

<script type="text/javascript">
$('.sourceTip').popover({
    trigger: 'hover',
    title : 'Threat Actions',
    content : 'Threat Actions describe what the threat agent did to cause or contribute to the breach (the methods used). There are usually multiple actions during a breach scenario. Verizon uses seven primary categories of threat actions, which are described below. For each category, there is a series of metrics pertaining to the specific type, path or vector, and other relevant details.'
});
$('.agentTip').popover({
    trigger: 'hover',
    title : 'Agent',
    content : 'Agent refers to entities that cause or contribute to the incident. There can be more than one agent involved in any incident and their role can be malicious or non-malicious, intentional or accidental, direct or indirect. VERIS recognizes 3 primary categories of threat agents - External, Internal, and Partner. Each of these includes several category-specific metrics. '
});
$('.integrityLossTip').popover({
    trigger: 'hover',
    title : 'Integrity Loss',
    content : 'Refers to an asset (or data) being complete and unchanged from the original state. Losses to integrity include unauthorized insertion, modification, etc.'
});
$('.authenticityLossTip').popover({
    trigger: 'hover',
    title : 'Authenticity Loss',
    content : 'Refers to the validity, conformance, correspondence to intent, and genuineness of the asset (or data). Losses of authenticity include misrepresentation, repudiation, misappropriation, etc.'
});$('.availabilityLossTip').popover({
    trigger: 'hover',
    title : 'Availability Loss',
    content : 'Refers to an asset (or data) being present and ready for use when needed. Losses to availability include destruction, deletion, movement, performance impact (delay or acceleration), and interruption. Metrics pertaining to availability identify the duration of the asset was affected.'
});
$('.utilityLossTip').popover({
    trigger: 'hover',
    title : 'Utility Loss',
    content : 'Refers to the usefulness or fitness of the asset (or data) for a purpose. Losses of utility include obscuration and conversion to a less useable or indecipherable form. Utility is distinguished from availability in that the data are still present but no longer (as) useable. '
});
$('.actionToDiscoveryTip').popover({
    trigger: 'hover',
    title : 'Action to discovery',
    content : 'Provides insight into the skill of the agent (i.e., to elude discovery), nature of the action, and detective capabilities the organization. Notes Refers to the span of time from when the incident occurs to when the victim learns of the incident.'
});
$('.discoveryToContainmentTip').popover({
    trigger: 'hover',
    title : 'Discovery to containment',
    content : 'Provide insight into the readiness and capability of the organization to respond to and recover from an incident. Notes Refers to span of time from when the incident is discovered to when it is contained (i.e., the “bleeding is stopped”) or restored (i.e., fully functional).'
});
$('.evidenceSourcesTip').popover({
    trigger: 'hover',
    title : 'Evidence Sources',
    content : 'Provides information useful to improving incident response and detection capabilities. Potential source for indicators of an incident. Notes Identify the main (or most informative) sources of evidence that were used during the response process or investigation of this incident. This may include application, system, and device logs, emails, browsing history, etc. '
});
$('.otherDiscoveryMetricsTip').popover({
    trigger: 'hover',
    title : 'Other Discovery Metrics',
    content : 'Other Discovery metrics to consider · Greater specificity. Metrics in this section are designed to give a high-level understanding of how the incident was discovered. However, given the rather atrocious track record with respect to incident discovery evidenced in the DBIR, we have a great deal to research and learn here. An incident response program should collect as much information as possible here to identify successes, failures, and opportunities.'
});
$('.2020hindsightTip').popover({
    trigger: 'hover',
    title : '20/20 Hindsight',
    content : 'Uses the benefit of post-incident analysis to identify how the incident could have been prevented. It is both informative and beneficial in that it gets to the crux of the matter. Notes Knowing what you know now, what is the most straightforward, efficient, yet effective way this incident could have been prevented?'
});
$('.correctiveActionTip').popover({
    trigger: 'hover',
    title : 'Corrective Action',
    content : 'Identifies what should be done to prevent such an incident from recurring in the future. Notes What should be done to help ensure an incident like this does not happen again? This should include general recommendations, specific changes to policy, procedures, personnel, and technology, short-term and long-term strategies, etc. '
});
$('.assetLossTip').popover({
    trigger: 'hover',
    title : 'Asset Loss',
    content : 'Assesses losses directly associated with damaged, abused, stolen (etc) assets. Includes lost or damaged assets, stolen funds, cash outlays, etc.'
});
$('.businessDisruptionTip').popover({
    trigger: 'hover',
    title : 'Business Disruption',
    content : 'Assesses losses an organization encounters due to an inability (or reduced ability) to carry out their value to the market. This may include “soft” costs like unproductive man-hours or lost revenue due to system downtime.'
});
$('.responseCostTip').popover({
    trigger: 'hover',
    title : 'Response/Recovery Cost',
    content : 'Assesses costs associated with resolving an incident and getting things back to normal. Includes cost of response, investigation, containment, remediation, restoration, etc.'
});
$('.overallImpactTip').popover({
    trigger: 'hover',
    title : 'Overall Impact',
    content : 'To understand the severity of the impact relative to the organization’s tolerance for loss. Insignificant - Impact absorbed by normal activities Distracting - Limited "hard costs" but impact felt through having to deal with the incident rather than conducting normal duties Painful - Real but limited (scope, longevity) losses in the form of fines, penalties, market corrections, payouts, productivity losses, etc Damaging - Real and serious effect on the "bottom line" and/or long-term ability to generate revenue.'
});
</script>