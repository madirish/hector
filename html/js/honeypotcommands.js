$(function(){	
	var commands = $.parseJSON($('#connections').text());
	console.log(commands);
	commandsColumns = [
	                   {data: 'id',"visible":false},
	                   {data: 'time'},
	                   {data: 'ip'},
	                   {data: 'session_id'},
	                   {data: 'command'},
	                   ];
	$('#commands-table').DataTable({
		data:commands,
		columns:commandsColumns,
		"sDom": '<"top"lf>rt<"bottom"ip>',
		
	})
})