$(document).ready(function(){
	$('#incident-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
			});
	$('#article-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
		"order": [[0,"desc"]],
			});
	$('#vuln-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
			});
	$('#host-table').dataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
			});
})