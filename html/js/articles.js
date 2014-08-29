$(document).ready(function(){
	var table = $('#articles-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
		"order": [[0,"desc"]],
		"columnDefs": [
		               {"width": "10%", "targets": 0},
		               {"width": "40%", "targets": 3},
		               {"width": "13%", "targets": 4}]
	});
	
	var labels = $.parseJSON($('#topic-labels').text());
	var values = $.parseJSON($('#topic-data').text());
	var data = {labels: labels,
            datasets: [
                {
                    label: "My First dataset",
                    fillColor: "#05EDFF",
                    strokeColor: "#05EDFF",
                    pointColor: "#05EDFF",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: values,
                }
            ]
};
var options = {
    multiTooltipTemplate: "<%= datasetLabel%> - <%= value %>",
    responsive: true,
    scaleFontColor: "#000",
    
};
var myNewChart = new Chart(document.getElementById("top-topic").getContext("2d")).Bar(data, options);
	
})
