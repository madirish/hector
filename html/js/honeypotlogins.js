$(function(){
	var raw = $('#login-attempts').text();
	var data = JSON.parse(raw);
	console.log(data);
	columns = [
	           {data: 'id', "visible":false},
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
})