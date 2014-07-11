$(function(){
	var raw = $('#login-attempts').text();
	console.log(raw);
	var data = JSON.parse(raw);
	columns = [
	           {data: 'id', "visible":false},
	           {data: 'ip'},
	           {data: 'country_code'},
	           {data: 'time'},
	           {data: 'username'},
	           {data: 'password'},
	           
	           ];
	console.log(data);
	$('#logins-table').DataTable({
		data:data,
		columns:columns,
		"sDom": '<"top"lf>rt<"bottom"ip>',
	})
})