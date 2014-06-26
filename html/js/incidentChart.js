/*
 * incidentChart.js
 * @author: Ubani Anthony Balogun
 * @date: June 19, 2014
 * @requires: Chart.js, legend.js
 * 
 * Doughnut Chart implementation for Hector summary page
 */
$(document).ready(function(){
	var chartLabels = JSON.parse(document.getElementById('incidentChartLabels').textContent);
	var chartData = JSON.parse(document.getElementById('incidentChartCounts').textContent);
	var colors = ["#F7464A","#E2EAE9","#D4CCC5","#949FB1","#4D5360","#F38630","#E0E4CC","#69D2E7","#D97041","#C7604C"];
	var data = [];
	for (x in chartLabels){
		var label = chartLabels[x];
		if (chartData.hasOwnProperty(label)){
			var count = chartData[label];       
		}else{
			var count =  0;
		}
		data.push({value:count,color:colors[x],title:label})
	}
	var options = {percentageInnerCutout: 50}
	var ctx = document.getElementById("incidentChart").getContext("2d");
	ctx.canvas.width = 300;
	ctx.canvas.height = 300;
	var incidentChart = new Chart(ctx).Doughnut(data,options);
	legend(document.getElementById("incidentChartLegend"),data)
	
})