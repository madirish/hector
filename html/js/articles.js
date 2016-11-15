// Depends on hector.analytics.js

$(document).ready(function(){
	var table = $('#articles-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
		"order": [[0,"desc"]],
		"columnDefs": [
		               {"width": "10%", "targets": 0},
		               {"width": "40%", "targets": 3},
		               {"width": "13%", "targets": 4}]
	});
	
	hectorDrawBarChart('top-topic','topic-labels','topic-data');
})
