$(document).ready( function () {
	
	if (document.getElementById('dhost4') != null){
	    var table = $('#dhost4').DataTable({
	        "ordering": true,
	        "autoWidth": false,
	    });
	    table.column('0:visible').order('asc');
	    table.draw();
	}
	
	if (document.getElementById('dhost7') != null){
	    var table = $('#dhost7').DataTable({
	        "ordering": true,
	        "autoWidth": false,
	    });
	    table.column('0:visible').order('asc');
	    table.draw();
	}
} );