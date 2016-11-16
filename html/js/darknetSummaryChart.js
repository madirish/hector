$(document).ready(function(){
    if (document.getElementById('darknetSummaryChartLabels') !== null) {
        var displayLabels = document.getElementById('darknetSummaryChartLabels').textContent.split(",");
        var dataPoints = document.getElementById('darknetSummaryChartData').textContent.split(",");
        
        var data = {
                labels : displayLabels,
                datasets : [{ 
                    //fillColor : "rgba(200,220,160,0.2)",
                    //strokeColor : "rgba(200,220,160,1)",
                    data : dataPoints,
                }],
                borderColor: "#000000",
        };
        var options = {
                legend: {
                    display: false,
                }
        };
	
        var specs = {
            type: 'bar',
            data: data,
            options: options
         };
         var darknetSummaryChart = new Chart($("#darknetChart"), specs);
     }
});  