<h2>OSSEC Hosts</h2>
<div class="row">
	<div class="span12">
		<table id="ossec-table" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>IP</th>
				<th>Name</th>
				<th>Link</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($hosts as $host): ?>
				<tr>
					<td><?php echo $host['ip']?></td>
					<td><?php echo $host['name']?></td>
					<td><?php echo $host['name_linked']?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
	</div>
</div>
