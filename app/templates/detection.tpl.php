<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>

<div class="row">
<div class="span4">
<p class="lead">Port Probes Yesterday</p>
<table class="table">
<tr><th>Hits</th><th>Port</th><th>Protocol</th></tr>
<?php

foreach ($port_result as $row) {
	echo "<tr><td>" . $row->cid . "</td><td><a href='?action=reports&report=by_port&ports=" . $row->dst_port . "'>" . $row->dst_port . "</a></td><td>" . $row->proto . "</td></tr>";
}
?>
</table>

</div><div class="span4">

<p class="lead">Latest 20 distinct darknet probe IPs</p>
<table class="table">
<tr><th>IP Address</th></tr>
<?php

foreach ($darknet_result as $row) {
	echo "<tr><td><a href='?action=attackerip&ip=" . $row->evilip . "'>" . $row->evilip . "</a> (" . gethostbyaddr($row->evilip) . ")</td></tr>";
}
?>
</table>

</div><div class="span4">
<p class="lead">Latest 30 attackers detected by OSSEC</p>
<table class="table">
<tr><th>IP Address</th></tr>
<?php

foreach ($ossec_attackers as $row) {
	echo "<tr><td><a href='?action=attackerip&ip=" . $row->evilip . "'>" . $row->evilip . "</a> (" . gethostbyaddr($row->evilip) . ")</td></tr>";
}
?>

</td></tr>
</table>

</div></div>