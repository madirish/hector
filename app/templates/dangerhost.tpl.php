<div id="content">
<h3>Dangerous Hosts</h3>
<?php if (count($sevenporthosts) > 0) { ?>
<h4>Hosts with more than 7 open ports</h4>
<table id="dhost" class="table table-striped">
<thead>
	<tr>
		<th>Host</th>
		<th>IP</th>
		<th>Support Group</th>
		<th>Open Ports</th>
	</tr>
</thead>
<tbody>
	<?php foreach ($sevenporthosts as $host) { ?>
	<tr>
		<td><?php echo $host->get_name_linked();?></td>
		<td><?php echo $host->get_ip();?></td>
		<td><?php echo $host->get_supportgroup_name();?></td>
		<td><?php echo join(',', $host->get_open_ports_array());?></td>
	</tr>
	<?php } ?>
</tdbody>
</table>

<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#dhost').DataTable({
        "ordering": true,
    });
    table.column('0:visible').order('asc');
    table.draw();
} );
</script>

<?php } ?>
<?php echo $content; ?>
</div>