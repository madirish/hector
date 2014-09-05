/*
 * incidentChart.js
 * @author: Ubani Anthony Balogun
 * @date: June 19, 2014
 * @requires: Chart.js, legend.js
 * 
 * Pie Chart implementation for Hector summary page
 */
$(document).ready(function(){
	if (document.getElementById('incidentReportHeader') !== null) {
		var chartHeader = JSON.parse(document.getElementById('incidentReportHeader').textContent);
		var chartLabels = JSON.parse(document.getElementById('incidentChartLabels').textContent);
		var chartData = JSON.parse(document.getElementById('incidentChartCounts').textContent);
		var colors = ["#F8FF01","#FF0F00","#69D2E7","#FF6600","#b0de09","#DDDDDD","#FFCC99","#D97041","#C7604C","#CCCFFF"];
		var data = [];
		
		for (var i = 0; i < chartLabels.length; i++){
			var label = chartLabels[i];
			var count = chartData[label]['count'];
			data.push({value:count , color:colors[i] ,title:label + " - " + count});
		}
		
		document.getElementById("incidentChartHeader").innerHTML = chartHeader;
		var ctx = document.getElementById("incidentChart").getContext("2d");
		ctx.canvas.width = 300;
		ctx.canvas.height = 300;
		var incidentChart = new Chart(ctx).Pie(data);
		legend(document.getElementById("incidentChartLegend"),data);
		
		
		var i = 0;
		$("#incidentChartLegend .title").each(function(index){
			$(this).attr('onclick', 'location.href="?action=incident_reports&threat_action=' + chartData[chartLabels[i]]['href'] + '"');
			i++;
		});
	}
})