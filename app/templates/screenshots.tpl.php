<h2> Host Screenshots - under development</h2>
<div class="row">
<div class="span12">
	<table id="screenshot-table" class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>Host ID</th>
			<th>Host name</th>
			<th>Screenshot</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($screenshots as $host_id=>$screenshots) { 
		?>
		<tr>
			<td><?php echo $host_id?></td>
			<td><?php echo $screenshots['name']?></td>
			<td><?php echo $screenshots['screenshot']?></td>
		</tr>
		<?php }?>
	</tbody>
	</table>
</div>
</div>