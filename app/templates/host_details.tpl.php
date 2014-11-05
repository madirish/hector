<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>

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
			if (basename($url[1]) !== 'screenshots' && $url[1] != '')  {
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
		<th class="span2">Vulnerability</th>
		<th class="span2">Description</th>
		<th class="span1">Dicovered</th>
		<th class="span1">Status</th>
	</tr>
</thead>
<tbody>
<?php foreach($vulns as $vuln) { ?>
	<tr><td><a href=?action=vuln_details&id=<?php echo $vuln->vuln_detail_id;?>><?php echo $vuln->vuln_name;?></a></td>
	<td><?php echo substr($vuln->vuln_detail_text,0,200);?></td>
	<td><?php echo $vuln->vuln_detail_datetime;?></td>
	<td><?php echo ($vuln->vuln_detail_fixed==1 ? 'Fixed <i class="icon-ok"></i>':'');?>
	    <?php echo ($vuln->vuln_detail_ignore==1 ? 'Ignored <i class="icon-ok"></i>':'');?></td></tr>
<?php } ?> 
</tbody>
</table>
</div>
</div>
<div colspan="12"><table><tr>
    <td class="editcell"><a class="btn" title="Edit this item" href="?action=add_edit&object=host&id=<?php echo $host->get_id();?>">Edit</a></td>
    <td class="deletecell"><a href="#deleteModal<?php echo $host->get_id();?>" role="button" class="btn" data-toggle="modal">Delete</a></td>
</tr></table>
</div>

<!-- Delete Modal -->
<div id="deleteModal<?php echo $host->get_id();?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="deleteModalLabel">Confirm delete!</h3>
</div>
<div class="modal-body">
<p><i class="icon-warning-sign"></i> You are about to <em>permanently</em> delete this record.  This cannot be undone.  Please confirm that you wish to proceed.</p>
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
<a class="btn btn-danger" href="?action=delete&object=host&id=<?php echo $host->get_id();?>">Confirm delete</a>
</div>
</div>
