<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>

<table>
<tr><td style="border:1px solid black;">

<H2>Port Probes Yesterday</h2>
<table>
<tr><th>Hits</th><th>Port</th><th>Protocol</th></tr>
<?php

foreach ($port_result as $row) {
	echo "<tr><td>" . $row->cid . "</td><td><a href='?action=reports&report=by_port&ports=" . $row->dst_port . "'>" . $row->dst_port . "</a></td><td>" . $row->proto . "</td></tr>";
}
?>
</table>

</td><td style="border:1px solid black; padding-left: 20px;">

<H2>Latest 20 distinct darknet probe IPs</h2>
<table>
<tr><th>IP Address</th></tr>
<?php

foreach ($darknet_result as $row) {
	echo "<tr><td><a href='?action=attackerip&ip=" . $row->evilip . "'>" . $row->evilip . "</a> (" . gethostbyaddr($row->evilip) . ")</td></tr>";
}
?>
</table>

</td><td style="border:1px solid black; padding-left: 20px;">
<H2>Latest 30 attackers detected by OSSEC</h2>
<table>
<tr><th>IP Address</th></tr>
<?php

foreach ($ossec_attackers as $row) {
	echo "<tr><td><a href='?action=attackerip&ip=" . $row->evilip . "'>" . $row->evilip . "</a> (" . gethostbyaddr($row->evilip) . ")</td></tr>";
}
?>

</td></tr>
</table>

</tr></td></table>