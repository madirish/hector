$(document).ready(function(){
	var displayLabels = document.getElementById('portSummaryChartLabels').textContent.split(",");
	var dataPoints = document.getElementById('portSummaryChartData').textContent.split(",");

    var data = {
        labels : displayLabels,
        datasets : [ 
            { 
            fillColor : "rgba(220,220,220,0.2)",
            strokeColor : "rgba(220,220,220,1)",
            data : dataPoints
            }
        ]
    }
    var myNewChart = new Chart(document.getElementById("topPortsChart").getContext("2d")).Bar(data);
})