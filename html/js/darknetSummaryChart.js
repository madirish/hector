$(document).ready(function(){
	if (document.getElementById('darknetSummaryChartLabels') !== null) {
		var displayLabels = document.getElementById('darknetSummaryChartLabels').textContent.split(",");
		var dataPoints = document.getElementById('darknetSummaryChartData').textContent.split(",");
	
	    var data = {
	        labels : displayLabels,
	        datasets : [ 
	            { 
	            fillColor : "rgba(200,220,160,0.2)",
	            strokeColor : "rgba(200,220,160,1)",
	            data : dataPoints
	            }
	        ]
	    }
	    var myNewChart = new Chart(document.getElementById("darknetChart").getContext("2d")).Bar(data);
		$("#darknetChart").click(function (evt) {
			var activeBars = myNewChart.getBarsAtEvent(evt);
			location.href="?action=reports&report=by_port&ports=" + activeBars[0]["label"];
		});     
	}
})  