$(document).ready( function () {
    var table = $('#listtable').DataTable({
        "ordering": true,
        "columnDefs":[{"targets": -1, "orderable":false, "searchable":false,}]
    });
    table.column('0:visible').order('asc');
    table.draw();
});