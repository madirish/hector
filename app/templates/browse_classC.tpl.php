<h2>IP's in <?php echo htmlspecialchars($_GET['classC']);?>.x</h2>
<table id="tableClassC" name="tableClassC" class="table table-striped table-bordered">
<thead>
    <tr><th>Hostname</th><th>IP</th><th>Open Ports</th><th>OS</th></tr>
</thead>
<tbody>
<!-- This space filled dynamically via the JavaScript below -->
</tbody>
</table>

<script type="text/javascript">
var hosts='<?php echo json_encode($outputs); ?>';
var working = $.parseJSON(hosts);
$(function() {
	$.each(working, function(i, item) {
		var href = '<a href="?action=host_details&id=' + item.id + '">' + item.name + '</a>';
		table.row.add([href, item.ip, item.ports, item.os]).draw().node();
	});
});
</script>
