<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>
<?php
if (isset($_GET['host_group_id'])) { // Specific Host Group
?>
<h3><?php echo $hostgroup->get_name();?> Hostgroup Members</h3>
<table id="hostgroupstable">
<thead>
<tr>
    <th>Hostname</th>
    <th>IP</th>
    <th>OS</th>
    <th>Support Group</th>
</tr>
</thead>
<?php
	foreach ($hosts as $host) {?>
<tr>
    <td><a href='?action=host_details&id=<?php echo $host->get_id();?>'><?php echo $host->get_name();?></a></td>
    <td><?php echo $host->get_ip();?></td>
    <td><?php echo $host->get_os();?></td>
    <td><?php echo $host->get_supportgroup_name();?></td>
</tr>
	<?php } ?>
</table>
<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#hostgroupstable').DataTable({
        "ordering": true,
    });
    table.column('0:visible').order('asc');
    table.draw();
} );
</script>
<?php
}
else { // Show all groups ?> 
<h3>Host Groups</h3>
<table id="hostgroupstable" class="table table-striped">
<thead>
<tr>
    <th>Name</th>
    <th width="50%">Description</th>
    <th># of hosts</th>
    <th>&nbsp;</th>
</tr>
</thead>
<?php
    foreach ($hostgroups as $hostgroup) {?>
<tr>
    <td><?php echo $hostgroup->get_name();?></td>
    <td><?php echo $hostgroup->get_detail();?></td>
    <td><?php echo count($hostgroup->get_host_ids());?></td>
    <td>
        <a href="?action=host_groups&host_group_id=<?php echo $hostgroup->get_id();?>"><input type="button" class="btn btn-info" value="Details"/></a>
        <a href="?action=config&object=host_group"><input type="button" class="btn" value="Config"/></a>
    </td>
</tr>
    <?php } ?>
</table>
<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#hostgroupstable').DataTable({
        "ordering": true,
        "aoColumnDefs": [
          { 'bSortable': false, 'aTargets': [ 3 ] } /** No sort on button column **/
       ]
    });
    table.column('0:visible').order('asc');
    table.draw();
} );
</script>
<?php } ?>