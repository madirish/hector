$(document).ready(function(){
	$('#ports-yday').dataTable({
		"sDom": '<"top"f>rt<"bottom"p>',
		"order": [[0,"desc"]],
		"iDisplayLength": 25,
			});
	$('#distinct-probes').dataTable({
		"sDom": '<"top"f>rt<"bottom"p>',
		"iDisplayLength": 25,
			});
	$('#attackers').dataTable({
		"sDom": '<"top"f>rt<"bottom"p>',
		"iDisplayLength": 25,
			});
})