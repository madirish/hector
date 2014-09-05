/**
 * Requires hector.analytics.js
 */

$(function(){
	
	hectorDrawDoughnutChart("top-country","countrypercent");
	hectorDrawDoughnutChart("top-ip","ippercent");
	hectorDrawDoughnutChart("top-user","userpercent");
	hectorDrawDoughnutChart("top-pass","passpercent");
	hectorDrawDoughnutChart("sess-ip","sess-ippercent");
	hectorDrawDoughnutChart("sess-country","sess-cpercent");
	
	var raw = $('#login-attempts').text();
	var data = JSON.parse(raw);
	columns = [
	           {data: 'id'},
	           {data: 'ip_linked'},
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
	                   {data: 'ip_linked'},
	                   {data: 'session_id'},
	                   {data: 'command'},
	                   ];
	$('#commands-table').DataTable({
		data:commands,
		columns:commandsColumns,
		"sDom": '<"top"lf>rt<"bottom"ip>',
	});
	
	hectorDrawBarChart('top-commands','top-commands-labels','top-commands-values');
	
});