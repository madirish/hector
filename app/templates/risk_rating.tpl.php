<h3><?php echo ucfirst($risk->get_name());?> Risk Vulnerabilities</h3>

<div class="row">
	<div class="span12">
		<table id="articles-table" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Vulnerability</th>
					<th>Host</th>
					<th>Observed</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($vuln_details as $detail): ?>
					<tr>
						<td><?php echo $detail->get_vuln_name()?></td>
						<td><a href="?action=host_details&id=<?php echo $detail->get_host_id();?>"><?php echo $detail->get_host_name();?></a></td>
						<td><?php echo $detail->get_datetime(); ?></td>
						<td><?php echo substr($detail->get_text(), 0, 200); ?></td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>