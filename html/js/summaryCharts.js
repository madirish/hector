/*
 * incidentChart.js
 * @author: Ubani Anthony Balogun
 * @author: Justin C. Klein Keane
 * @date: 6 November 2014
 * @requires: Chart.js, legend.js
 * 
 * Pie Chart implementation for Hector summary page
 */
 
function prepPieChart(pieChartId, pieChartLegend, pieCharthartLabels, pieChartCounts) {
	if (document.getElementById(pieChartId) !== null) {
		var chartLabels = JSON.parse(document.getElementById(pieCharthartLabels).textContent);
		var chartData = JSON.parse(document.getElementById(pieChartCounts).textContent);
		var colors = ["#F8FF01","#FF0F00","#69D2E7","#FF6600","#b0de09","#DDDDDD","#FFCC99","#D97041","#C7604C","#CCCFFF"];
		var data = [];
		
		for (var i = 0; i < chartLabels.length; i++){
			var label = chartLabels[i];
			var count = chartData[label]['count'];
			data.push({value:count , color:colors[i] ,title:label + " - " + count});
		}
		
		var ctx = document.getElementById(pieChartId).getContext("2d");
		ctx.canvas.width = 300;
		ctx.canvas.height = 300;
		var incidentChart = new Chart(ctx).Pie(data, {
				animation:false, 
				percentageInnerCutout : 5,
				segmentStrokeColor: "#999",
				});
		legend(document.getElementById(pieChartLegend),data);
	
		// Hyperlink the legend items
		var i = 0;
		var titleTarget = "#" + pieChartLegend + " .title";
		$(titleTarget).each(function(index){
			$(this).attr('onclick', 'location.href="' + chartData[chartLabels[i]]['href'] + '"');
			i++;
		});
	}
}

$(document).ready(function(){
	prepPieChart('incidentChart','incidentChartLegend','incidentChartLabels','incidentChartCounts');
	prepPieChart('vulnNumbersChart','vulnNumbersChartLegend','vulnNumbersChartLabels','vulnNumbersChartCounts');
	prepPieChart('incidentAssetChart','incidentAssetLegend','incidentAssetLabels','incidentAssetCounts');
})