$(function(){
	var raw = $('#login-attempts').text();
	console.log(raw);
	var data = JSON.parse(raw);
	columns = [
	           {data: 'ip'},
	           {data: 'username'},
	           {data: 'password'},
	           
	           ];
	console.log(data);
	$('#logins-table').DataTable({
		data:data,
		columns:columns,
	})
})