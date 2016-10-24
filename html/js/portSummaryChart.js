$(document).ready(function(){
	if (document.getElementById('portSummaryChartLabels') !== null) {
		var displayLabels = document.getElementById('portSummaryChartLabels').textContent.split(",");
		var dataPoints = document.getElementById('portSummaryChartData').textContent.split(",");
	
	    var data = {
	    	type: 'horizontalBar',
	    	data: {
	        labels : displayLabels,
	        datasets : [{ 
	            fillColor : "rgba(120,220,220,0.2)",
	            strokeColor : "rgba(120,220,220,1)",
	            borderColor : "rgba(0,0,0,1)",
	            borderWidth: 1,
	            data : dataPoints
	            }]
	    	},
	    	options: { legend: {display: false}, borderColor: '#000' }
	    }
	    var portSummaryChart = new Chart($("#topPortsChart"), data);
		$("#topPortsChart").click(function (evt) {
			var activeBars = portSummaryChart.getBarsAtEvent(evt);
			location.href="?action=reports&report=by_port&ports=" + activeBars[0]["label"];
		});
	}
});
