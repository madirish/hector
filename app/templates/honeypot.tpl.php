<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>

<ul class="nav nav-tabs" id="honeypotTabs">	
	<li class="active"><a href="#logins"data-toggle="tab">Login Attempts</a></li>
	<li><a href="#sessions"data-toggle="tab">Sessions</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane active" id="logins">
<p class="lead">Recent Login Attempts</p>
<table class="table">
<tr><th>IP</th><th>Hostname</th><th>Time</th><th>Username</th><th>Password</th></tr>
<?php

foreach ($login_attempts as $row) {
	echo "<tr><td><a href='?action=attackerip&ip=" . $row->ip . "'>".$row->ip."</a></td>";
	echo "<td>" . gethostbyaddr($row->ip) . "</td>";
	echo "<td>" . $row->time . "</td><td>" . $row->username . "</td>";
	echo "<td>" . $row->password . "</tr>";
}
?>
</table>

</div>
<div class="tab-pane" id="sessions">
<p class="lead">Sessions Yesterday</p>
<table class="table">
<tr><th>Time</th><th>IP</th><th>Hostname</th><th>Command</th></tr>
<?php

foreach ($commands as $row) {
	echo "<tr><td>" . $row->time . "</td>";
	echo "<td><a href='?action=attackerip&ip=" . $row->ip . "'>".$row->ip."</a></td>";
	echo "<td>" . gethostbyaddr($row->ip) . "</td>";
	echo "<td>" . $row->command . "</td></tr>";
}
?>
</table>

</div></div>

