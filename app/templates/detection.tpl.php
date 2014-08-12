<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>

<div class="row">
<div class="span4">
<p class="lead">Port Probes Yesterday</p>
<table id="ports-yday" class="table table-bordered table-striped">
<thead>
	<tr>
		<th>Hits</th>
		<th>Port</th>
		<th>Protocol</th>
	</tr>	
</thead>
<tbody>
	<?php foreach ($port_result as $row):?>
		<tr>
			<td><?php echo $row->cid; ?></td>
			<td><a href="?action=reports&report=by_port&ports=<?php echo $row->dst_port;?>"><?php echo $row->dst_port;?></a></td>
			<td><?php echo $row->proto;?></td>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>

</div><div class="span4">

<p class="lead">Latest 20 distinct darknet probe IPs</p>
<table id="distinct-probes" class="table table-bordered table-striped">
<thead>
	<tr>
		<th>IP Address</th>
	</tr>
</thead>
<tbody>
	<?php foreach ($darknet_result as $row):?>
		<tr>
			<td><a href="?action=attackerip&ip=<?php echo $row->evilip;?>"><?php echo $row->evilip?></a> (<?php echo gethostbyaddr($row->evilip)?>)</td>
		</tr>
	<?php endforeach;?>
</tbody>
</table>

</div><div class="span4">
<p class="lead">Latest 30 attackers detected by OSSEC</p>
<table id="attackers" class="table">
<thead>
	<tr>
		<th>IP Address</th>
	</tr>
</thead>
<tbody>
	<?php foreach($ossec_attackers as $row):?>
		<tr>
			<td><a href="?action=attackerip&ip=<?php echo $row->evilip?>"><?php echo $row->evilip?></a> (<?php echo gethostbyaddr($row->evilip)?>)</td>
		</tr>
	<?php endforeach;?>
</tbody>
</table>

</div></div>