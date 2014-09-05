$(document).ready( function () {
    var table1 = $('#tabledarknet_drops').DataTable({
        "ordering": true
    });
    table1.column('0:visible').order('desc');
    table1.draw();

    var table2 = $('#ossecalerttable').DataTable({
        "ordering": true
    });
    table2.column('0:visible').order('desc');
    table2.draw();
} );