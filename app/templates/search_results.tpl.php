<div id="content">
<h2>Search Results</h2>
Your search returned <span class="badge"><?php echo count($hosts);?></span> results.

<table id="tableSearchResults" name="tableSearchResults" class="table table-striped table-bordered">
<thead>
    <tr>
        <th>Hostname</th>
        <th>ip</th>
        <th>Sponsor</th>
        <th>Technical</th>
        <th>Notes</th>
    </tr>
</thead>
<tbody>
<?php if (is_array($hosts)):?>
	<?php foreach ($hosts as $host):?>
		<tr>
			<td><?php echo $host->get_name_linked();?></td>
			<td><?php echo $host->get_ip();?></td>
			<td><?php echo $host->get_sponsor();?></td>
			<td><?php echo $host->get_technical();?></td>
			<td><?php echo $host->get_note();?></td>
		</tr>
	<?php endforeach;?>
<?php endif;?>
</tbody>
</table>
</div>
<br/></br>