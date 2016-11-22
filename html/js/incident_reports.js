/**
 * Requires hector.analytics.js
 */

$(document).ready(function () {
	hectorDrawDoughnutChart("threat-agent","agentpercent",$("#topThreatAgent").text());
	hectorDrawDoughnutChart("threat-action","actionpercent",$("#topThreatAction").text());
	hectorDrawDoughnutChart("threat-asset", "assetpercent",$("#topAssetAffected").text());
	hectorDrawDoughnutChart("disco-method", "discopercent",$("#topDiscoveryMethod").text());
	
	var labels = $.parseJSON($('#incident-chart-labels').text());
	var values = $.parseJSON($('#incident-chart-data').text());
	
	var data = {
			labels: labels,
            datasets: [
                {
                    label: "Reported security incidents",
                    lineTension: 0,
                    backgroundColor: "#f0fbff",
                    pointRadius: 5,
                    pointBackgroundColor: "#2e94b9",
                    borderColor: "#999",
                    borderWidth: 1,
                    data: values,
                }
            ]
	};
	
	var options = {};

	var myIncidentChart = new Chart($("#incidentCountChart"), {
		type: 'bar',
		data: data,
		options: options
	});
	
	 var table = $('#incidenttable').dataTable({
	        "ordering": true,
	        "order": [[0,"desc"]],
	        'autoWidth': false,
	        "columnDefs":[
	                      {"width": "10%", "targets": 0},
	                      {"width": "20%", "targets": 1},
	                      {"targets": -1, "orderable": false, "searchable": false, "width": "10%"}],
	    });
	    
});