/*
 * incidentChart.js
 * @author: Justin C. Klein Keane
 * @date: November 4, 2014
 * @requires: Chart.js, legend.js
 * 
 * Pie Chart implementation for Hector summary page
 */
$(document).ready(function(){
	if (document.getElementById('vulnReportHeader') !== null) {
		var chartHeader = JSON.parse(document.getElementById('vulnReportHeader').textContent);
		var chartLabels = JSON.parse(document.getElementById('vulnChartLabels').textContent);
		var chartData = JSON.parse(document.getElementById('vulnChartCounts').textContent);
		var colors = ["#F8FF01","#FF0F00","#69D2E7","#FF6600","#b0de09","#DDDDDD","#FFCC99","#D97041","#C7604C","#CCCFFF"];
		var data = [];
		
		for (var i = 0; i < chartLabels.length; i++){
			var label = chartLabels[i];
			var count = chartData[label]['count'];
			data.push({value:count , color:colors[i] ,title:label + " - " + count});
		}
		
		document.getElementById("vulnChartHeader").innerHTML = chartHeader;
		var ctx = document.getElementById("vulnChart").getContext("2d");
		ctx.canvas.width = 300;
		ctx.canvas.height = 300;
		var incidentChart = new Chart(ctx).Pie(data,{percentageInnerCutout : 50});
		legend(document.getElementById("vulnChartLegend"),data);
		
		
		var i = 0;
		$("#vulnChartLegend .title").each(function(index){
			$(this).attr('onclick', 'location.href="?action=vulnerability&id=' + chartData[chartLabels[i]]['href'] + '"');
			i++;
		});
	}
})