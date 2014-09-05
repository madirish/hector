$(document).ready(function(){
	var table = $('#tablealerts').DataTable({
    	"ordering": true
    });
    table.column('0:visible').order('desc');
    table.draw();
})