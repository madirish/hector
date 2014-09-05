$(document).ready( function () {
    var table = $('#tableClassC').DataTable({
        "ordering": true,
        "autoWidth": false,
    });
    table.column('0:visible').order('desc');
    table.draw();
} );