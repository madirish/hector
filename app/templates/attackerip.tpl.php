<script type="text/javascript" src="js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<div id="content">
<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
<p class="lead">Report for <?php echo htmlspecialchars($ip) . ' - ' . gethostbyaddr($ip);?></p>
<div class="well well-small">
	<h3>Honeypot logins</h3>
	<p>This ip has attempted <strong><?php echo $login_attempts; ?></strong> logins on the honeypot.</p>
</div>
<div class="well well-small">
<h3>Darknet sensors</h3>
Your search returned <?php echo count($darknet_drops);?> results from darknet sensors.
<?php 
if (count($darknet_drops) > 0) {
	echo $content;	
}
?>
</div>
<h3>OSSEC alerts</h3>
<table class="table table striped">
<thead>
<tr><th>Alert date</th><th>Alert level</th><th>Log entry</th></tr>
</thead><tbody>
<?php
foreach ($ossec_alerts as $alert) {
	echo "<tr><td>" . $alert->alert_date . "</td>";
	echo "<td>" . $alert->rule_level . "</td>";
	echo "<td>" . htmlspecialchars($alert->rule_log) . "</td></tr>\n";
}
?>
</tbody>
</table>
</div>