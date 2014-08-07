<h2><?php echo isset($tag_name) ? $tag_name: "";?></h2>
<h3><?php echo "Related Incidents"?></h3>
<div class="row">
<!-- Incidents Related to the tag -->
<div class="span12">
	<table id="incident-table" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Incident id</th>
				<th>Year - Month</th>
				<th>Title</th>
				<th>Agent</th>
				<th>Threat action</th>
				<th>Asset Affected</th>
				<th>Overall Impact</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($incidents as $incident): ?>
				<tr>
					<td><?php echo $incident['id']?></td>
					<td><?php echo $incident['year'] . " - " . $incident['month_friendly']?>
					<td><a href="?action=incident_report_summary&id=<?php echo $incident['id']?>"><?php echo $incident['title'];?></a></td>
					<td><?php echo $incident['agent'];?></td>
					<td><?php echo $incident['action']?></td>
					<td><?php echo $incident['asset']?></td>
					<td><?php echo $incident['impact']?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
</div>
<h3><?php echo "Related Articles"?></h3>
<div class="row">
<!-- Articles Related to the tag -->
<div class="span12">
	<table id="article-table" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Date</th>
				<th>Title</th>
				<th>URL</th>
				<th>Teaser</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($articles as $article): ?>
				<tr>
					<td><?php echo $article['date']?></td>
					<td><?php echo $article['linked_title']?></td>
					<td><?php echo $article['linked_url']?></td>
					<td><?php echo $article['teaser']?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
</div>