

<?php if (isset($message)):?>
	<div id="message" class="alert"><?php echo $message;?></div>
<?php endif;?>

<?php if (isset($_GET['host_group_id'])):?>
	<h3><?php echo $prefix . " " . $hostgroup->get_name();?> Hostgroup Members    <small><?php echo $filter;?></small></h3>

<ul class="nav nav-tabs" id="hostGroupTab">
	<li class="active"><a href="#overview" data-toggle="tab">Overview</a></li>
	<li><a href="#os" data-toggle="tab">OS Breakdown</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane active" id="overview">

	<table id="hostgroupstable" class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>Hostname</th>
		    <th>IP</th>
		    <th>OS</th>
		    <th>Support Group</th>
		    <th style="display:none"><!-- Buffer for DataTables --></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($hosts as $host):?>
			<tr>
				<td><a href='?action=host_details&id=<?php echo $host->get_id();?>'><?php echo $host->get_name();?></a></td>
				<td><?php echo $host->get_ip();?></td>
			    <td><?php echo $host->get_os();?></td>
			    <td><?php echo $host->get_supportgroup_name();?></td>
			    <td style="display:none"><!-- Buffer for DataTables --></td>
			</tr>
		<?php endforeach;?>
	</tbody>
	</table>
	
</div>
<div class="tab-pane" id="os">
	<div id="osVersions-div" class="chart-container">
		<div id="osVersionsChartLegend"></div>
		<canvas id="osVersions" widht="300"></canvas>
	</div>
</div>
</div>
	
	<script type="text/javascript">
		function legend(parent, data) {
		    parent.className = 'chart-legend';
		    var datas = data.hasOwnProperty('datasets') ? data.datasets : data;
	
		    datas.forEach(function(d) {
		        var title = document.createElement('span');
		        title.className = 'title';
		        title.style.borderColor = d.hasOwnProperty('strokeColor') ? d.strokeColor : d.color;
		        title.style.borderStyle = 'solid';
		        parent.appendChild(title);
	
		        var text = document.createTextNode(d.title);
		        title.appendChild(text);
		    });
		}
	
		$(document).ready(function(){
			var pieChartId = 'osVersions';
			var chartLabels = [<?php echo $oscountlabels; ?>];
			var chartData = {<?php echo $oscountdata; ?>};
			var colors = [<?php echo $oscountcolors; ?>];
			var data = [];
			
			for (var i = 0; i < chartLabels.length; i++){
				var label = chartLabels[i];
				var count = chartData[label]['count'];
				data.push({value:count , color:colors[i] ,title:label + " - " + count});
			}
			
			var ctx = document.getElementById(pieChartId).getContext("2d");
			ctx.canvas.width = 300;
			ctx.canvas.height = 300;
			var incidentChart = new Chart(ctx).Pie(data, {
					animation:false, 
					percentageInnerCutout : 0,
					segmentStrokeColor: "#000",
					segmentStrokeWidth: 1,
					});
			var pieChartLegend = document.getElementById('osVersionsChartLegend');
			legend(pieChartLegend,data);
		
			// Hyperlink the legend items
			var i = 0;
			var titleTarget = "#osVersionsChartLegend .title";
			$(titleTarget).each(function(index){
				$(this).attr('onclick', 'location.href="' + chartData[chartLabels[i]]['href'] + '"');
				i++;
			});
		});
		
	</script>
<?php else:?>
	<h3>Host Groups</h3>
	<table id="hostgroupstable" class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>Name</th>
		    <th width="50%">Description</th>
		    <th># of hosts</th>
		    <th># of live hosts</th>
		    <th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($hostgroups as $hostgroup): ?>
			<tr>
			    <td><?php echo $hostgroup->get_name();?></td>
			    <td><?php echo $hostgroup->get_detail();?></td>
			    <td><?php echo count($hostgroup->get_host_ids());?></td>
			    <td><?php echo count($hostgroup->get_live_host_ids());?></td>
			    <td>
			        <a href="?action=host_groups&live=yes&host_group_id=<?php echo $hostgroup->get_id();?>"><input type="button" class="btn btn-info" value="Details"/></a>
			        <a href="?action=config&object=host_group"><input type="button" class="btn" value="Config"/></a>
			    </td>
			</tr>
		<?php endforeach;?>
	</tbody>
	</table>
<?php endif;?>

