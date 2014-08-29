<div id="content">
<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
<?php if ($ip !== "") { ?>
<p class="lead">Report for <?php echo $ip_rpt_display;?></p>
<div class="well well-small">
	<h4>Honeypot logins</h4>
	<p>This ip has <span class="badge badge-info"><?php echo $login_attempts; ?></span> failed logins on the honeypot.</p>
	<p>This ip has issued <span class="badge badge-info"><?php echo $commands; ?></span> commands on the honeypot.</p>
</div>
<div class="well well-small">
<h4>Darknet sensors</h4>
Your search returned <?php echo count($darknet_drops);?> results from darknet sensors.
<table id="tabledarknet_drops" name="tabledarknet_drops" class="table table-striped">
<thead>
    <tr><th>Attacker IP</th><th>Target IP</th><th>Source Port</th><th>Destination Port</th><th>Protocol</th><th>Observed at:</th></tr>
</thead>
<tbody>
<?php 
if (count($darknet_drops) > 0) {
foreach ($darknet_drops as $drop) {
    echo '<tr><td>' . $ip . '</td>';
    echo '<td>' . $drop->dst_ip . '</td>';
    echo '<td>' . $drop->src_port . '</td>';
    echo '<td>' . $drop->dst_port . '</td>';
    echo '<td>' . $drop->proto . '</td>';
    echo '<td>' . $drop->received_at . '</td>';
    echo '</tr>';
}}
?>
</tbody></table>
</div>
<div class="well">
<h4>OSSEC alerts</h4>
<table id="ossecalerttable" name="ossecalerttable" class="table table striped">
<thead>
<tr><th>Alert date</th><th>Alert level</th><th>Log entry</th></tr>
</thead><tbody>
<?php
if (count($ossec_alerts) > 0) {
foreach ($ossec_alerts as $alert) {
	echo "<tr><td>" . $alert->alert_date . "</td>";
	echo "<td>" . $alert->rule_level . "</td>";
	echo "<td>" . htmlspecialchars($alert->rule_log) . "</td></tr>\n";
}}
?>
</tbody>
</table>
</div>
<?php } ?>
</div>