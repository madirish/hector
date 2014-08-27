$(document).ready(function(){
	if (document.getElementById('portSummaryChartLabels') !== null) {
		var displayLabels = document.getElementById('portSummaryChartLabels').textContent.split(",");
		var dataPoints = document.getElementById('portSummaryChartData').textContent.split(",");
	
	    var data = {
	        labels : displayLabels,
	        datasets : [ 
	            { 
	            fillColor : "rgba(120,220,220,0.2)",
	            strokeColor : "rgba(120,220,220,1)",
	            data : dataPoints
	            }
	        ]
	    }
	    var myNewChart = new Chart(document.getElementById("topPortsChart").getContext("2d")).Bar(data);
		$("#topPortsChart").click(function (evt) {
			var activeBars = myNewChart.getBarsAtEvent(evt);
			location.href="?action=reports&report=by_port&ports=" + activeBars[0]["label"];
		});
	}
});
