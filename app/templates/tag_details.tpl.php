<h2><?php echo isset($tag_name) ? $tag_name: "";?></h2>
<div class="row">
<!-- Analytics wells -->
<div class="span3 pagination-centered">
        <div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Incidents with tag</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="tag-incidents"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo 'Of all Incidents' ?></h4></div>
        	<div class="hidden" id="incidentpercent"><?php echo $incidentpercent;?></div>
        </div>
    </div>
<div class="span3 pagination-centered">
        <div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Articles with tag</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="tag-articles"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo 'Of all Articles' ?></h4></div>
        	<div class="hidden" id="articlepercent"><?php echo $articlepercent;?></div>
        </div>
    </div>
<div class="span3 pagination-centered">
        <div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Vulnerabilities with tag</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="tag-vulns"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo 'Of all Vulnerabilities' ?></h4></div>
        	<div class="hidden" id="vulnpercent"><?php echo $vulnpercent;?></div>
        </div>
    </div>
<div class="span3 pagination-centered">
        <div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Hosts with tag</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="tag-hosts"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo 'Of all Hosts' ?></h4></div>
        	<div class="hidden" id="hostpercent"><?php echo $hostpercent;?></div>
        </div>
    </div>
</div>

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
<h3><?php echo "Related Vulnerabilities"?></h3>
<div class="row">
<!-- Vulnerabilities Related to tag -->
<div class="span12">
	<table id="vuln-table" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Id</th>
				<th>Name</th>
				<th>Description</th>
				<th>CVE</th>
				<th>OSVDB</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($vulns as $vuln): ?>
				<tr>
					<td><?php echo $vuln['id']?></td>
					<td><?php echo $vuln['name']?></td>
					<td><?php echo $vuln['description']?></td>
					<td><?php echo $vuln['cve']?></td>
					<td><?php echo $vuln['osvdb']?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
</div>
<!-- Host with tag -->
<h3>Hosts with this tag</h3>
<div class="row">
<div class="span12">
	<table id="host-table" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Hostname</th>
				<th>IP</th>
				<th>OS</th>
				<th>Support Group</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($hosts as $host):?>
				<tr>
					<td><?php echo $host['name_linked']?></td>
					<td><?php echo $host['ip']?></td>
					<td><?php echo $host['os']?></td>
					<td><?php echo $host['support_group']?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
</div>