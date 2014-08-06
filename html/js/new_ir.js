$(function(){
	var availableTags = $.parseJSON($("#availableTags").text());
	function split( val ) {
        return val.split( /,\s*/ );
        }
	function extractLast( term ) {
		return split( term ).pop();
		}
      
	$("#incidentTags").bind("keydown", function(event){
		if ( event.keyCode === $.ui.keyCode.TAB &&
	            $( this ).autocomplete( "instance" ).menu.active ) {
	          event.preventDefault();
	        }
	}).autocomplete({
		minLength:0,
		source: function( request, response ) {
	          response( $.ui.autocomplete.filter(
	            availableTags, extractLast( request.term ) ) );
	        },
		focus: function() {
			return false;
		},
		select: function( event, ui ) {
			var terms = split( this.value );
			terms.pop();
			terms.push( ui.item.value );
			terms.push( "" );
			this.value = terms.join( ", " );
			return false;
	        }
	})
	// Tooltips
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
	$('.tagTip').popover({
	    trigger: 'hover',
	    title : 'Tags',
	    content : 'A comma separated list of tags that are relevant to the incident'
	});
})