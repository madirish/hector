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
		<p class="lead"> Recent Login Attempts</p>
		<div class="row">
		<div class="span3">
			<div class="well">
				<h4>Top Country</h4>
				<p><?php echo $c_percent . "% " . $c_top;?></p>
			</div>
		</div>
		<div class="span3">
			<div class="well">
				<h4>Top IP</h4>
				<p><?php echo $ip_percent . "% " . $ip_top;?></p>
			</div>
		</div>
		<div class="span3">
			<div class="well">
				<h4>Top Username</h4>
				<p><?php echo $u_percent . "% " . $u_top;?></p>
			</div>
		</div>
		<div class="span3">
			<div class="well">
				<h4>Top Passwords</h4>
				<p><?php echo $pass_percent . "% " . $pass_top;?></p>
			</div>
		</div>
		</div>
		<div class="hidden" id="login-attempts"><?php echo htmlentities($attempts_json); ?></div>
		<div class="dataTables_wrapper form-inline no-footer">
			<table id="logins-table" class="table table-striped table-bordered table-responsive">
				<thead>
					<tr>
						<th>ID</th>
						<th>IP</th>
						<th>Country Code</th>
						<th>Time</th>
						<th>Username</th>
						<th>Password</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
	
	<div class="tab-pane" id="sessions">
		<p class="lead">Recent Sessions</p>
		<div id="connections" class="hidden"> <?php echo htmlentities($commands_json);?></div>
		<div class="dataTables_wrapper form-inline no-footer">
			<table id="commands-table" class="table table-striped table-bordered table-responsive">
				<thead>
					<tr>
						<th>ID</th>
						<th>Time</th>
						<th>IP</th>
						<th>Session ID</th>
						<th>Command</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>


