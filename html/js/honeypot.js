$(function(){
	var raw = $('#login-attempts').text();
	var data = JSON.parse(raw);
	columns = [
	           {data: 'id'},
	           {data: 'ip'},
	           {data: 'country_code'},
	           {data: 'time'},
	           {data: 'username'},
	           {data: 'password'},
	           ];
	$('#logins-table').DataTable({
		data:data,
		columns:columns,
		"sDom": '<"top"lf>rt<"bottom"ip>',
	})
	
	var commands = $.parseJSON($('#connections').text());
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