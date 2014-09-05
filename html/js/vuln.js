$(document).ready(function(){
	    var table = $('#vulns').DataTable({
        "ordering": true,
        "aoColumnDefs": [
          { 'bSortable': false, 'aTargets': [ 3,4 ] } ] /** No sort on button column **/
    });
    table.column('2:visible').order('desc');
    table.draw();
})