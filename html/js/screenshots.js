$(document).ready(function(){
	$('#screenshot-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
		"columns":[{"width":"10%"},
		           {"width":"20%"},
		           {"width":"70%"}]
			});
})