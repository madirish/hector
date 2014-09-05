$(document).ready( function () {
	if (document.getElementById('tableSearchResults') != null){
	    var table = $('#tableSearchResults').DataTable({
	        "ordering": true,
	        "autoWidth": false,
	    });
	    table.draw();
	}
} );