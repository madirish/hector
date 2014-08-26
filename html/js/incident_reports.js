/**
 * Requires hector.analytics.js
 */

$(document).ready(function () {
	
     
	hectorDrawDoughnutChart("threat-agent","agentpercent");
    
	hectorDrawDoughnutChart("threat-action","actionpercent");
	hectorDrawDoughnutChart("threat-asset", "assetpercent");
	hectorDrawDoughnutChart("disco-method", "discopercent");
});