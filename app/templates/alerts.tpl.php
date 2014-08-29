<p class="lead">Port Change Alerts</p>

<table id="tablealerts" name="tablealerts" class="table table-striped table-bordered">
<thead>
<tr>
	<th>Timestamp</th>
	<th>Message</th>
	<th>Asset</th>
</tr>
</thead>
<tbody>
<?php if (isset($alerts) && is_array($alerts)):?>
	<?php foreach ($alerts as $alert):?>
		<tr>
			<td><?php echo $alert->get_timestamp();?></td>
			<td><?php echo $alert->get_string();?></td>
			<td><?php echo $alert->get_host_linked();?></td>
		</tr>
	<?php endforeach;?>
<?php endif;?>
</tbody>
</table>