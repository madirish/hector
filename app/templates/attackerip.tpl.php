<script type="text/javascript" src="js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<div id="content">
<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
<h2>Report for <?php echo htmlspecialchars($ip) . ' - ' . gethostbyaddr($ip);?></h2>
<h3>Honeypot logins</h3>
<p>This ip has attempted <b><?php echo $login_attempts; ?></b> logins on the honeypot.</p>
<h3>Darknet sensors</h3>
Your search returned <?php echo count($darknet_drops);?> results from darknet sensors.
<?php 
if (count($darknet_drops) > 0) {
	echo $content;	
}
?>
<h3>OSSEC alerts</h3>
<table>
<tr><th>Alert date</th><th>Alert level</th><th>Log entry</th></tr>
<?php
foreach ($ossec_alerts as $alert) {
	echo "<tr><td style='padding-right: 10px;border-right: solid 1px black;'>" . $alert->alert_date . "</td>";
	echo "<td style='padding-right: 10px;border-right: solid 1px black;'>" . $alert->rule_level . "</td>";
	echo "<td>" . htmlspecialchars($alert->rule_log) . "</td></tr>\n";
}
?>
</table>
</div>