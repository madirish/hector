<div id="content">
<h3>Dangerous Hosts</h3>
<?php if (count($sevenporthosts) > 0) { ?>
<h4>Hosts with more than 7 open ports</h4>
<table id="dhost7" class="table table-striped table-bordered">
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
</tbody>
</table>

<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#dhost7').DataTable({
        "ordering": true,
    });
    table.column('0:visible').order('asc');
    table.draw();
} );
</script>

<?php } ?>
<?php if (count($fourporthosts) > 0) { ?>
<h4>Hosts with more than 4 <a href="#dangerModal" data-toggle="modal" title="About server ports">server</a> ports open:</h4>
<div id="dangerModal" class="modal hide fade" role="dialog" aria-labelledby="dangerModal" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	<h3 id="dangerLabel">Server Ports</h3>
	</div>
	<div class="modal-body">
	<p>Server ports are defined as port 21 (ftp), 22 (ssh), 23 (telnet), 25 (smtp), 53 (DNS), 80 (http), 110 (POP3), 143 (IMAP), 443 (https), 993 (IMAPS), 1433 (MS-SQL), 1521 (Oracle SQL), 3306 (MySQL), and 8080 (http).</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>

<table id="dhost4" class="table table-striped table-bordered">
<thead>
	<tr>
		<th>Host</th>
		<th>IP</th>
		<th>Support Group</th>
		<th>Open Ports</th>
	</tr>
</thead>
<tbody>
	<?php foreach ($fourporthosts as $host) { ?>
		<tr>
		<td><?php echo $host->get_name_linked();?></td>
		<td><?php echo $host->get_ip();?></td>
		<td><?php echo $host->get_supportgroup_name();?></td>
		<td><?php echo join(',', $host->get_open_ports_array());?></td>
	</tr>
	<?php } ?>
</tbody>
</table>



<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#dhost4').DataTable({
        "ordering": true,
    });
    table.column('0:visible').order('asc');
    table.draw();
} );
</script>
<?php } ?>
<?php if (count($sevenporthosts) < 1 && count($fourporthosts) < 1) {?>
No <a href="#dangerModal" data-toggle="modal" title="About dangerous hosts">dangerous hosts</a> detected by port scans.
<div id="dangerModal" class="modal hide fade" role="dialog" aria-labelledby="dangerModal" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	<h3 id="dangerLabel">Dangerous Hosts</h3>
	</div>
	<div class="modal-body">
	<p>Dangerous hosts are hosts with more than multiple ports open as detected by an Nmap scan indicating a lack of firewall or firewall misconfiguration.</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>
<?php } ?>
</div>