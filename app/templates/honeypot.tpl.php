<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>

<div class="row">
<div class="span6">
<p class="lead">Recent Login Attempts</p>
<table class="table">
<tr><th>IP</th><th>Time</th><th>Username</th><th>Password</th></tr>
<?php

foreach ($login_attempts as $row) {
	echo "<tr><td><a href='?action=attackerip&ip=" . $row->ip . "'>".$row->ip."</a>";
	echo " (" . gethostbyaddr($row->evilip) . ")";
	echo "</td><td>" . $row->time . "</td><td>" . $row->username . "</td>";
	echo "<td>" . $row->password . "</tr>";
}
?>
</table>

</div><div class="span6">

<p class="lead">Sessions Yesterday</p>
<table class="table">
<tr><th>Time</th><th>IP</th><th>Command</th></tr>
<?php

foreach ($commands as $row) {
	echo "<tr><td>" . $row->time . "</td>";
	echo "<td><a href='?action=attackerip&ip=" . $row->ip . "'>".$row->ip."</a></td>";
	echo "<td>" . $row->command . "</td></tr>";
}
?>
</table>

</div></div>