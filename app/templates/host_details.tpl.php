<div class="row">
<div class="span5">
<table id="host_details" class="table table-bordered">
	<tr id="name">
		<td>Hostname</td>
		<td><?php echo $host->get_name();?></td>
	</tr>
	<tr id="ip">
		<td>IP Address</td>
		<td><?php echo $host->get_ip();?></td>
	</tr>
	<tr id="ip">
		<td>Operating System</td>
		<td><?php echo $host->get_os();?></td>
	</tr>
	<tr id="technical">
		<td>Technical contact:</td>
		<td><?php echo $host->get_technical();?></td>
	</tr>
	<tr id="sponsor">
		<td>Sponsor:</td>
		<td><?php echo $host->get_sponsor();?></td>
	</tr>
	<tr id="location">
		<td>Location:</td>
		<td><?php echo $host->get_location_name();?></td>
	</tr>
	<tr id="supportgroup">
		<td>Support Group:</td>
		<td><?php echo $host->get_supportgroup_name();?></td>
	</tr>
	<tr id="link">
		<td>External URL:</td>
		<td><a href="<?php echo $host->get_link();?>"><?php echo $host->get_link();?></a>
		</td>
	</tr>
	<tr id="notes">
		<td>Notes:</td>
		<td><?php echo $host->get_note();?></td>
	</tr>
	<tr id="policy">
		<td>Covered by policy:</td>
		<td><?php echo ($host->get_policy()) ? 'Yes' : 'No';?> </td>
	</tr>
	<tr id="excluded">
		<td>Excluded from portscan alerts?:</td>
		<td><?php echo ($host->get_portscan_exclusion()) ? 'Yes' : 'No';?></td>
	</tr>
	<tr id="tags">
		<td>Tags:</td>
		<td><?php echo $tags;?></td>
	</tr>
<?php
if ($host->get_portscan_exclusion()) {?>
	<tr id="excludedby">
		<td>Excluded by:</td>
		<td><?php echo $host->get_excludedby_name();?></td>
	</tr>
	<tr id="excludedon">
		<td>Excluded on:</td>
		<td><?php echo $host->get_excludedon();?></td>
	</tr>
	<tr id="excludedfor">
		<td>Excluded for:</td>
		<td><?php echo ($host->get_excludedfor() < 0) ? 'forever' : $host->get_excludedfor() . ' days';?></td>
	</tr>
	<tr id="excludedreason">
		<td>Reason:</td>
		<td><?php echo $host->get_excludedreason();?></td>
		</tr>
<?php } ?>
	<tr id="groups">
		<td><a href="?action=details&object=host_group">Host groups</a>:</td>
		<td><?php echo $host->get_host_groups_readable();?></td>
	</tr>
</table>

</div>
<div class="span6">
<table class="table table-striped table-bordered">
<thead>
	<tr>
		<th>Port</th>
		<th>State</th>
		<th>Date</th>
		<th>Protocol</th>
		<th>Version</th>
	</tr>
</thead>
<tbody>
<?php
		if (isset($scans->members) && is_array($scans->members)) {
			foreach ($scans->members as $scan) echo $scan->get_details();
		}
?>
</tbody></table>

</div>
</div>

<div class="row">
<div class="span5">
<table id="screenshotstable" class="table table-striped table-bordered">
<thead>
	<tr>
		<th>URL</th>
		<th>Screenshot</th>
		</tr>
</thead>
<tbody>
<?php		
foreach($host->get_urls() as $url) {
?>
	<tr>
		<td><?php echo $url[0];?></td>
		<td><?php
			if (basename($url[1]) !== 'screenshots')  {
				?>
				<a href='?action=display_screenshot&ajax&url=<?php echo urlencode($url[0]);?>'>
				<img width=150 alt="Screenshot" src='?action=display_screenshot&ajax&url=<?php echo urlencode($url[0]);?>'/>
				</a>
				<?php 	
			}
			else { 
				echo 'No image available';
			}
			?>
		</td>
	</tr>
<?php } ?>
</tbody></table>
</div>
<div class="span6">
<table id="vulntable" class="table table-striped table-bordered">
<thead>
	<tr>
		<th>Vulnerability Type</th>
		<th>Text</th>
		<th>Dicovered</th>
		<th>Fixed</th>
		<th>Ignore</th>
	</tr>
</thead>
<tbody>
<?php foreach($vulns as $vuln) { ?>
	<tr><td><a href=?action=vuln_details&id=<?php echo $vuln->vuln_detail_id;?>><?php echo $vuln->vuln_name;?></a></td>
	<td><?php echo $vuln->vuln_detail_text;?></td>
	<td><?php echo $vuln->vuln_detail_datetime;?></td>
	<td><?php echo ($vuln->vuln_detail_fixed==1 ? '<i class="icon-ok"></i>':'');?></td>
	<td><?php echo ($vuln->vuln_detail_ignore==1 ? '<i class="icon-ok"></i>':'');?></td></tr>
<?php } ?> 
</tbody>
</table>
</div>
</div>