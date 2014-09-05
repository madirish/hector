<form method="post" action="?action=attackerip" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
Search malicious IP database: <input type="text" name="ip"/> <input type="submit" value="Search"/><br/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
</form>
<h2>Honeypot Summary</h2>
<ul class="nav nav-tabs" id="honeypotTabs">	
	<li class="active"><a href="#logins"data-toggle="tab">Login Attempts</a></li>
	<li><a href="#sessions"data-toggle="tab">Sessions</a></li>
</ul>


<div class="tab-content">
	<div class="tab-pane active" id="logins">
		<p class="lead"> Recent Login Attempts</p>
		<div class="row">
		<div class="span3 pagination-centered">
			<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top Country</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="top-country"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $c_top; ?></h4></div>
        	<div class="hidden" id="countrypercent"><?php echo $c_percent;?></div>
        	</div>
		</div>
		<div class="span3 pagination-centered">
			<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top IP</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="top-ip"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $ip_top; ?></h4></div>
        	<div class="hidden" id="ippercent"><?php echo $ip_percent;?></div>
			</div>
		</div>
		<div class="span3 pagination-centered">
			<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top Username</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="top-user"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $u_top; ?></h4></div>
        	<div class="hidden" id="userpercent"><?php echo $u_percent;?></div>
			</div>
		</div>
		<div class="span3 pagination-centered">
			<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top Password</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="top-pass"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $pass_top; ?></h4></div>
        	<div class="hidden" id="passpercent"><?php echo $pass_percent;?></div>
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
		<div class="row">
			<div class="span3 pagination-centered">
				<div class="panel panel-default">
	        		<div class="panel-heading">
	        			<h4 class="panel-title">Top IP</h4>
	        		</div>
	        	<div class="panel-body">
	        		<canvas id="sess-ip"></canvas>		
	        	</div>
        	<div class="panel-footer"><h4><?php echo $sip_top; ?></h4></div>
        	<div class="hidden" id="sess-ippercent"><?php echo $sip_percent;?></div>
			</div>
			</div>
			<div class="span3 pagination-centered">
			<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top Country</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="sess-country"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $scount_top; ?></h4></div>
        	<div class="hidden" id="sess-cpercent"><?php echo $scount_percent;?></div>
        	</div>
		</div>
		<div class="span6 pagination-centered">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title"> Top Commands</h4>
				</div>
				<div class="panel-body">
					<canvas id="top-commands" width="500" height="260"></canvas>
					<div id="top-commands-labels" class="hidden"><?php echo $labels;?></div>
					<div id="top-commands-values" class="hidden"><?php echo $data;?></div>
				</div>
			</div>
		</div>
		</div>
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


