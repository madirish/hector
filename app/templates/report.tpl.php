<div id="content">
<?php if (! isset($search_results)): ?>
<form method="post" action="?action=reports&report=by_port" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
<fieldset>
	<legend>Find hosts with:</legend>
	<table>
		<tr><td>Any of these TCP ports:</td><td><input type="text" name="anyports"/> (comma separated list)</td></tr>
		<tr><td>Any of these UDP ports:</td><td><input type="text" name="anyUDPports"/> (comma separated list)</td></tr>
		<tr><td>All of these TCP ports:</td><td><input type="text" name="allports"/> (comma separated list)</td></tr>
		<tr><td>All of these UDP ports:</td><td><input type="text" name="allUDPports"/> (comma separated list)</td></tr>
		<tr><td>None of these TCP ports:</td><td><input type="text" name="portsex"/> (comma separated list)</td></tr>
		<tr><td>None of these UDP ports:</td><td><input type="text" name="UDPportsex"/> (comma separated list)</td></tr>
		<tr><td>All of these tags:</td><td><select name="tagsin[]" size="4" multiple="multiple">
			<?php foreach ($tags as $tag) echo '<option value="' . $tag->get_id() . '">' . $tag->get_name() . '</option>'; ?>
			</select> (only search for machines with these tags)</td></tr>
		<tr><td>None of these Tags:</td><td><select name="tagsex[]" size="4" multiple="multiple">
			<?php foreach ($tags as $tag) echo '<option value="' . $tag->get_id() . '">' . $tag->get_name() . '</option>'; ?>
			</select> (do not report machines with these tags)</td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="Search" class="btn"/></td></tr>
	</table>
</fieldset>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
<?php else: ?>
<h4><?php echo count($host_results);?> records found</h4>
    <?php if (count($host_results) < 1):?>
    <p>No records found.  Perhaps you do not have permission to relevant hosts.</p>
    <?php else: ?>
    <form method="post" action="?action=host_groups" name="searchResultsToHostGroup" id="searchResultsToHostGroup">
    <input type="hidden" name="token" value="<?php echo $token;?>"/>
    <input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
    <table id="tablePortSearchResult" class="table table-striped">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th>Hostname</th>
        <th>Support Group</th>
        <th>IP Address</th>
        <th>Last seen on:</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($search_results as $host): ?>
    <tr>
        <td><input type="checkbox" name="host_id[]" value="<?php echo $host->get_id();?>"/></td>
        <td><?php echo $host->get_name_linked() ;?></td>
        <td><?php echo $host->get_supportgroup_name() ;?></td>
        <td><?php echo $host->get_ip() ;?></td>
       <td><?php echo $host->maxtime ;?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    <div class="input-prepend input-append">
    <span class="add-on">Add checked records to host group: </span><select name="hostgroup">
    <?php foreach ($hostgroups as $group):?>
        <option value="<?php echo $group->get_id();?>"><?php echo $group->get_name();?></option>
        <?php endforeach; ?>
    </select><input type="submit" value="Add" class="btn"/>
    </div>
    </form>
    <?php endif; ?>
<?php endif; ?>
</div>