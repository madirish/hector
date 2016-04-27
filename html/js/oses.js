$(document).ready(function(){
	var table = $('#oses-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
		"order": [[0,"desc"]],
		"columnDefs": [
		               {"width": "20%", "targets": 0},
		               {"width": "20%", "targets": 0},
		               {"width": "20%", "targets": 0},
		               {"width": "20%", "targets": 0},
		               {"width": "20%", "targets": 0}]
	});
	
	hectorDrawBarChart('top-os','os-labels','os-data');
})
