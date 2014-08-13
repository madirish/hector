$.fn.dataTable.ext.search.push(
		function(settings, data, dataIndex ){
			var min = parseInt($('#minlevel').val(),10);
			var level = parseFloat(data[4]) || 0;
			
			if( isNaN(min) || min <= level){
				return true;
			}
			return false;
		}
)

$.fn.dataTable.ext.search.push(
		function(settings, data, dataIndex){
			var ip = $('#ip').val();
			var regex = /\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/;
			var res = ip.match(regex);
			var ip_data = data[5] || 0;
			
			if (!ip || !res || ip == ip_data){
				return true;
			}
			return false;
		}
)

$(document).ready(function(){
	var table = $('#ossec-alerts-table').DataTable({
		"sDom": '<"top"lf>rt<"bottom"ip>',
		"order": [[0,"desc"]],
		"oLanguage": {
			"sSearch": "",
			"sLengthMenu": "_MENU_ entries"
		}
	
			});
	$('.dataTables_filter input').attr("placeholder","Search")
	
	$('#minlevel').keyup(function(){
		table.draw();
	})
	
	$('#ip').keyup(function(){
		table.draw();
	})
	
	$('#clearbtn').click(function(){
		$('#ip').val('');
		$('#minlevel').val('');
		table.draw();
	})
})