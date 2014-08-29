/*
 * assetsAffected.js
 * @author: Ubani Anthony Balogun
 * @date: July 24, 2014
 * @requires: Chart.js, legend.js, JQuery
 * 
 * Pie chart of assets affected for Hector Incident Reports
*/

$(document).ready(function(){
	if (document.getElementById('incident-assets-counts') !== null) {
		var assets_count = $.parseJSON($("#incident-assets-counts").text());
		var labels = $.parseJSON($("#incident-assets-labels").text());
		var colors = ["#F8FF01","#FF0F00","#69D2E7","#FF6600","#b0de09","#DDDDDD","#FFCC99","#DCB9B3","#C7604C","#C58EF8","#F9C7F5"];
		var data = [];
		
		for (var i = 0; i < labels.length; i++){
			var asset = labels[i];
			var count = assets_count[asset]['count'];
			data.push({value:count, color:colors[i], title:asset + " - " + count});
		}
		var ctx = $("#incident-assets-chart").get(0).getContext("2d");
		ctx.canvas.width = 300;
		ctx.canvas.height = 300;
		var assets_chart = new Chart(ctx).Pie(data);
		legend(document.getElementById("incident-assets-legend"),data);
		
		var i = 0;
		$("#incident-assets-legend .title").each(function(index){
			$(this).attr('onclick','location.href="' + assets_count[labels[i]]['href'] + '"');
			i++;
		});
	}
})