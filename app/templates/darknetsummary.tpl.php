<h2>Darknet Summary</h2>
<div class="row">
	<div class="span3 pagination-centered">
			<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top Destination Port</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="top-dst"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $dst_top; ?></h4></div>
        	<div class="hidden" id="dstpercent"><?php echo $dst_percent;?></div>
        	</div>
	</div>
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
        	<div class="panel-footer"><h4><?php echo long2ip($ip_top); ?></h4></div>
        	<div class="hidden" id="ippercent"><?php echo $ip_percent;?></div>
        </div>
	</div>
	<div class="span3 pagination-centered">
		<div class="panel panel-default">
	        	<div class="panel-heading">
	        		<h4 class="panel-title">Top Protocol</h4>
	        	</div>
        	<div class="panel-body">
        		<canvas id="top-proto"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $proto_top; ?></h4></div>
        	<div class="hidden" id="protopercent"><?php echo $proto_percent;?></div>
        </div>
		</div>
		
</div>

<table class="table table-striped table-condensed" id="darknet-probes-summary" name="darknet-probes-summary">
    <thead>
    <tr><th>Source IP</th><th>Protocol</th><th>Destination Port</th><th>Source Port</th><th>Country</th><th>Time</th></tr>
    </thead>
    <tbody>
    <?php foreach ($darknets as $probe): ?>
    	<?php $ip = long2ip($probe->get_src_ip()); ?>
    	<tr>
    		<td><a href="?action=attackerip&ip=<?php echo $ip ?>"><?php echo $ip?></a>
    		<td><?php echo $probe->get_proto()?></td>
    		<td><?php echo $probe->get_dst_port()?></td>
    		<td><?php echo $probe->get_src_port() ?></td>
    		<td><?php echo $probe->get_country_code()?></td>
    		<td><?php echo $probe->get_received_at()?></td>
    	</tr>
    <?php endforeach;?>
    </tbody>
</table>