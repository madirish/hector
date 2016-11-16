/*
 * incidentChart.js
 * @author: Ubani Anthony Balogun
 * @author: Justin C. Klein Keane
 * @date: 6 November 2014
 * @requires: Chart.js, legend.js
 * 
 * Pie Chart implementation for Hector summary page
 */
 
function prepPieChart(pieChartId, pieChartLegend, pieCharthartLabels, pieChartCounts,chartName) {
	if (document.getElementById(pieChartId) !== null) {
		var chartLabels = JSON.parse(document.getElementById(pieCharthartLabels).textContent);
		var chartData = JSON.parse(document.getElementById(pieChartCounts).textContent);
		var colors = ["#FF0F00","#F8FF01","#FF6600","#99ffcc","#b0de09","#DDDDDD","#FFCC99","#D97041","#C7604C","#CCCFFF"];
		var pieDataSets = [];
		var pieData = { 
				labels: chartLabels, 
				datasets: [{
					data: pieDataSets, 
					backgroundColor: colors,
					borderColor: "#999",
				}]
		};
		
		for (var i = 0; i < chartLabels.length; i++){
			var label = chartLabels[i];
			var count = chartData[label]['count'];
			pieDataSets.push(count);
		}
		
		DisplayData = {
				type: 'pie',
				data: pieData
		};
		chartName = new Chart($('#'+pieChartId),DisplayData);
	}
}

$(document).ready(function(){
	prepPieChart('incidentChart','incidentChartLegend','incidentChartLabels','incidentChartCounts','incidentChart');
	prepPieChart('vulnNumbersChart','vulnNumbersChartLegend','vulnNumbersChartLabels','vulnNumbersChartCounts','vulnChart');
	prepPieChart('incidentAssetChart','incidentAssetLegend','incidentAssetLabels','incidentAssetCounts','incidentChart');
})