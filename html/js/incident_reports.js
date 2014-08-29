/**
 * Requires hector.analytics.js
 */

$(document).ready(function () {
	hectorDrawDoughnutChart("threat-agent","agentpercent");
	hectorDrawDoughnutChart("threat-action","actionpercent");
	hectorDrawDoughnutChart("threat-asset", "assetpercent");
	hectorDrawDoughnutChart("disco-method", "discopercent");
	
	var labels = $.parseJSON($('#incident-chart-labels').text());
	var values = $.parseJSON($('#incident-chart-data').text());
	
	var data = {labels: labels,
            datasets: [
                {
                    label: "My First dataset",
                    fillColor: "rgba(255,255,255,0.1)",
                    strokeColor: "#05EDFF",
                    pointColor: "#05EDFF",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: values,
                }
            ]
	};
	var options = {
			bezierCurve: false,
			multiTooltipTemplate: "<%= datasetLabel%> - <%= value %>",
			};
	var myNewChart = new Chart(document.getElementById("incidentCountChart").getContext("2d")).Line(data, options);
	$("#incidentCountChart").hover(function (evt) {
   
		var activeBars = myNewChart.getPointsAtEvent(evt);
		});
	
});