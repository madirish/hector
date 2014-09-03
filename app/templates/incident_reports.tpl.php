<h2>Incident Reports</h2>
<div class="row-fluid">
    <div class="span3 pagination-centered">
        <div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Threat Agent</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="threat-agent"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $agent_names[0]; ?></h4></div>
        	<div class="hidden" id="agentpercent"><?php echo $agentpercent;?></div>
        </div>
    </div>
    <div class="span3 pagination-centered">
    	<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Threat Actions</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="threat-action"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $action_names[0]; ?></h4></div>
        	<div class="hidden" id="actionpercent"><?php echo $actionpercent;?></div>
        </div>
    </div>  
    <div class="span3 pagination-centered">
    	<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Assets Affected</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="threat-asset"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $asset_names[0]; ?></h4></div>
        	<div class="hidden" id="assetpercent"><?php echo $assetpercent;?></div>
        </div>
    </div>
    <div class="span3 pagination-centered">
    	<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Discovery Method</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="disco-method"></canvas>		
        	</div>
        	<div class="panel-footer"><h4><?php echo $disco_names[0]; ?></h4></div>
        	<div class="hidden" id="discopercent"><?php echo $discopercent;?></div>
        </div>
    </div>
</div>

<div class="row-fluid">
<div class="span12 pagination-centered">
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Timeline of Incident Reports</h3>
		
	</div>
	<div class="panel-body">
		<canvas id="incidentCountChart" height="300" width="600"></canvas>
		<div class="hidden" id="incident-chart-labels"><?php echo json_encode($chartlabels);?></div>
		<div class="hidden" id="incident-chart-data"><?php echo json_encode($chartvalues);?></div>
	</div>
</div>

</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<table id="incidenttable" name="incidenttable" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Date</th>
				<th>Title</th>
				<th>Agent</th>
				<th>Threat action</th>
				<th>Asset affected</th>
				<th>Overall impact</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $incidents as $incident):?>
				<tr>
					<td><?php echo $incident->get_year() . " - " . $incident->get_month_friendly();?></td>
					<td><a href="?action=incident_report_summary&id=<?php echo $incident->get_id()?>"><?php echo $incident->get_title();?></a></td>
					<td><?php echo $incident->get_agent()->get_name()?></td>
					<td><?php echo $incident->get_action()->get_action();?></td>
					<td><?php echo $incident->get_asset()->get_name();?></td>
					<td><?php echo $incident->get_impact_magnitude_friendly();?></td>
					<td><a href='<?php echo "#deleteModal" . $incident->get_id()?>' role='button' class='btn' data-toggle='modal'>Delete</a></td>
				</tr>
				<div id='<?php echo "deleteModal" . $incident->get_id();?>' role='button' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='<?php echo "deletemodal" . $incident->get_id();?>' aria-hidden='true'>
					<div class='modal-header'><h3>Are you sure?</h3></div>
					<div class='modal-body'><p>Are you sure you want to <em>permanently</em> delete this report?</p></div>
					<div class='modal-footer'>
						<button class='btn' data-dismiss='modal' aria-hidden='true'>No, return to view</button>
						<a href='?action=incident_report_delete&id=<?php echo $incident->get_id()?>' class='btn btn-primary'>Yes, delete!</a>
					</div>
				</div>
			<?php endforeach;?>
		</tbody>
		</table>
	</div>
</div>
