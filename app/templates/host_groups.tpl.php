<?php if (isset($message)):?>
	<div id="message" class="alert"><?php echo $message;?></div>
<?php endif;?>

<?php if (isset($_GET['host_group_id'])):?>
	<h3><?php echo $prefix . " " . $hostgroup->get_name();?> Hostgroup Members    <small><?php echo $filter;?></small></h3>
	<table id="hostgroupstable" class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>Hostname</th>
		    <th>IP</th>
		    <th>OS</th>
		    <th>Support Group</th>
		    <th style="display:none"><!-- Buffer for DataTables --></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($hosts as $host):?>
			<tr>
				<td><a href='?action=host_details&id=<?php echo $host->get_id();?>'><?php echo $host->get_name();?></a></td>
				<td><?php echo $host->get_ip();?></td>
			    <td><?php echo $host->get_os();?></td>
			    <td><?php echo $host->get_supportgroup_name();?></td>
			    <td style="display:none"><!-- Buffer for DataTables --></td>
			</tr>
		<?php endforeach;?>
	</tbody>
	</table>
<?php else:?>
	<h3>Host Groups</h3>
	<table id="hostgroupstable" class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>Name</th>
		    <th width="50%">Description</th>
		    <th># of hosts</th>
		    <th># of live hosts</th>
		    <th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($hostgroups as $hostgroup): ?>
			<tr>
			    <td><?php echo $hostgroup->get_name();?></td>
			    <td><?php echo $hostgroup->get_detail();?></td>
			    <td><?php echo count($hostgroup->get_host_ids());?></td>
			    <td><?php echo count($hostgroup->get_live_host_ids());?></td>
			    <td>
			        <a href="?action=host_groups&host_group_id=<?php echo $hostgroup->get_id();?>"><input type="button" class="btn btn-info" value="Details"/></a>
			        <a href="?action=config&object=host_group"><input type="button" class="btn" value="Config"/></a>
			    </td>
			</tr>
		<?php endforeach;?>
	</tbody>
	</table>
<?php endif;?>
