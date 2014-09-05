<h2>IP's in <?php echo htmlspecialchars($_GET['classC']);?>.x</h2>
<table id="tableClassC" name="tableClassC" class="table table-striped table-bordered">
<thead>
    <tr><th>Hostname</th><th>IP</th><th>Open Ports</th><th>OS</th></tr>
</thead>
<tbody>
	<?php if (isset($hosts) && is_array($hosts)):?>
		<?php foreach ($hosts as $host):?>
			<?php $name = ($host->get_name() !== '') ? $host->get_name() : $host->get_ip(); ?>
			<tr>
				<td><a href='?action=host_details&id=<?php echo $host->get_id();?>'><?php echo htmlspecialchars($name);?></a></td>
				<td><?php echo htmlspecialchars($host->get_ip());?></td>
				<td><?php echo $host->get_open_ports();?></td>
				<td><?php echo htmlspecialchars($host->get_os());?></td>
			</tr>
		<?php endforeach;?>
	<?php endif;?>
</tbody>
</table>
