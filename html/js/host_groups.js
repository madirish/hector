$(document).ready(function(){
	   var table = $('#hostgroupstable').dataTable({
	        "ordering": true,
	        "autoWidth": false,
	        "order": [[0,"asc"]],
	        "columnDefs": [
	                       {"targets": -1, "orderable": false, "searchable":false,}],
	    });
})