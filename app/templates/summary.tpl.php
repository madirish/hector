

<ul class="nav nav-tabs" id="vulnScanTab">
	<li class="active"><a href="#summary" data-toggle="tab">Overview</a></li>
	<li><a href="#incidents" data-toggle="tab">Incidents</a></li>
	<li><a href="#darknet" data-toggle="tab">Darknet</a></li>
	<li><a href="#honeypots" data-toggle="tab">Honeypots</a></li>
</ul>

<script type="text/javascript">
<!-- resize the worldmap when it is shown -->
var darknetResized = 0;
var kojoneyResized = 0;
$('a[data-toggle="tab"]').on('shown', function (e) {
    e.target // activated tab
    e.relatedTarget // previous tab

    var target = String(e.target).split("#");
    // alert("Showing " + target[1]);
    if (target[1] == "darknet") {
        // Annoyingly re-calling this function doubles the map
        if (darknetResized < 1) {
            showDarknetMap();
            darknetResized = 1;
        }
    }
    if (target[1] == "honeypots") {
        // Annoyingly re-calling this function doubles the map
        if (kojoneyResized < 1) {
        	initializeKojoneyMap();
        	//kojoneyResized = 1;
        }
    }
  })
</script>

<div class="tab-content">
<div class="tab-pane active" id="summary">

	<p class="lead"><?php echo $count;?> Hosts Tracked</p>
	<div id="addHostsModal" class="modal hide fade" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>No hosts tracked</h3>
	</div>
	<div class="modal-body">
	<p>You currently have no hosts tracked in the database.</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	<a href="?action=add_hosts" class="btn btn-primary">Go to add hosts</a>
	</div>
	</div>
	
	<div id="addScriptModal" class="modal hide fade" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>No scripts</h3>
	</div>
	<div class="modal-body">
	<p>You currently do not have any scanning scripts configured.  Scan scripts are required 
	to perform automated scans of hosts.</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	<a href="?action=config&object=scan_type" class="btn btn-primary">Go to script configuration</a>
	</div>
	</div>
	
	<div id="addScanModal" class="modal hide fade" aria-hidden="true">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>No scheduled scans</h3>
	</div>
	<div class="modal-body">
	<p>You currently do not have any scripts scheduled or scans configured.</p>
	</div>
	<div class="modal-footer">
	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	<a href="?action=config&object=scan" class="btn btn-primary">Go to scan schedule</a>
	</div>
	</div>

	<div class="row">
	  <div class="span6">
	  	<h3>Scanner:  Top Ports Detected</h3>
	  	<?php if ($portSummaryLabels !== '' && $portSummaryCounts !== ''): ?>
	  	<div id="portSummaryChartLabels" class="hidden"><?php echo $portSummaryLabels;?></div>
	  	<div id="portSummaryChartData" class="hidden"><?php echo $portSummaryCounts;?></div>
		<canvas id="topPortsChart" height="300" width="550"></canvas>
		<?php else: ?>
			No ports detected.
		<?php endif; ?>
	  </div>
	    <!-- Vulnerbility Breakdown Chart -->
	    <div class="span6"> 
	  		<?php if ($vuln_num_chart_labels !== '[]' && $vuln_num_chart_counts !== '[]'): ?>
	        <div id="vuln-numbers-div" class="chart-container">
	            <div id="vulnNumbersChartLabels" class="hidden"><?php echo $vuln_num_chart_labels?></div>
	            <div id="vulnNumbersChartCounts" class="hidden"><?php echo $vuln_num_chart_counts?></div>
	            <h3 id="vulnNumbersChartHeader"><?php echo $vuln_num_report_header?></h3>
	            <div id="vulnNumbersChartLegend"></div>
	            <canvas id="vulnNumbersChart" height="300" width="550"></canvas>
	        </div>
			<?php else: ?>
				No vulnerabilities tracked.
			<?php endif; ?>
	    </div>
	</div>
	
	
	<div class="row">

		<div class="span6">
	  		<div class="well">
	        <h4>Snapshot of Last 24 Hours</h4>
	        <ul>
	            <li><a href="?action=articles"><?php print_r( $article_count); ?> articles collected via RSS</a></li>
	            <li><a href="?action=ossecalerts"><?php echo $ossec_count; ?> OSSEC alerts</a></li>
	            <li><a href="?action=honeypot"><?php echo $honeypot_count; ?> honeypot logins</a></li>
	            <li><a href="?action=detection"><?php echo $probe_count; ?> port probes</a></li>
	        </ul>
	        </div>
		</div>
	    
	    <div class="span6">
	    <!-- Tag Cloud -->
	    <h3>Free Tags</h3>
	    	<div id="tagcloud">
	    		<?php foreach ($tag_cloud as $tag): ?>
	    			<a href="?action=tag_details&id=<?php echo $tag['id'];?>" rel="<?php echo $tag['weight'];?>"><?php echo $tag['name']?></a>
	    		<?php endforeach; ?>
	    	</div>
	    
	    </div>
	</div>
	<!--  Put in some OS breakdowns and then the tag cloud? -->
</div>
<div class="tab-pane" id="incidents">

	<div class="row">
	    <!-- Incident Pie Chart -->
	    <div class="span6"> 
	  		<?php if ($incidentchart_labels !== '[]' && $incidentchart_counts !== '[]'): ?>
	        <div id="incident-div" class="chart-container">
	            <div id="incidentChartLabels" class="hidden"><?php echo $incidentchart_labels?></div>
	            <div id="incidentChartCounts" class="hidden"><?php echo $incidentchart_counts?></div>
	            <h3 id="incidentChartHeader"><?php echo $incident_report_header?></h3>
	            <div id="incidentChartLegend"></div>
	            <canvas id="incidentChart"></canvas>
	        </div>
			<?php else: ?>
				No incidents reported.
			<?php endif; ?>
	    </div>
	    
	    <!-- Incident Assets Pie chart -->
		<div class="span6">
	  		<?php if ($asset_count_json !== '[]' && $asset_labels_json !== '[]'): ?>
			<div id="asset-chart-div" class="chart-container">
				<div id="incidentAssetCounts" class="hidden"><?php echo $asset_count_json?></div>
				<div id="incidentAssetLabels" class="hidden"><?php echo $asset_labels_json?></div>
				<h3 id="incidentAssetChartHeader"><?php echo $asset_count_header?></h3>
				<div id="incidentAssetLegend"></div>
				<canvas id="incidentAssetChart"></canvas>
			</div>
			<?php else: ?>
				No incidents reported.
			<?php endif; ?>
		</div>
	</div>

</div>

<div class="tab-pane" id="darknet">

	<div class="row">
	  
	  <div class="span6">
		<h3>Darknet:  Top Port Probes in Last 4 Days</h3>
	  	<?php if ($darknetSummaryLabels !== '' && $darknetSummaryCounts !== ''): ?>
		<canvas id="darknetChart"  height="300" width="550"></canvas>
		<div id="darknetSummaryChartLabels" class="hidden"><?php echo $darknetSummaryLabels;?></div>
	  	<div id="darknetSummaryChartData" class="hidden"><?php echo $darknetSummaryCounts;?></div>
		<?php else: ?>
			No port probes detected.
		<?php endif; ?>
		</div>
		
	    <!-- Darknet Probes Map -->	
		<div class="span6">	
		    <h3>Darknet:  Probes in Last 7 Days by Country</h3>
		    <div id="darknet-world-map" height="300" width="550" style="height:300px;width:500px;"></div>
		    <script type="text/javascript">
		    //@code_start
		    function showDarknetMap() {
	            var countryData = new Array();
		       <?php foreach ($darknetmapcounts as $key=>$val): ?>
		        countryData['<?php echo $key;?>']=<?php echo $val;?>;
		        <?php endforeach;?>
		       $('#darknet-world-map').vectorMap({
		           map: 'world_mill_en',
	    	        series: {
	    	            regions: [{
	    	                values: countryData,
	    	                scale: ['#C8EEFF', '#0071A4'],
	    	                normalizeFunction: 'polynomial'
	    	            }]
	    	        },
	    	        onRegionLabelShow: function(event, label, code){
	    	            label.html(
	    	                '<b>'+label.html()+'</b></br>'+countryData[code]+ ' probes'
	    	            );
	    	        },
	    	        onRegionClick: function (event, code) {
	    	            location.href = "?action=darknetsummary&country="+code;
	    	        },
	    	        onRegionOver: function(event,label){
	    				 $(this).css('cursor','pointer');
	    			 },
	    			 onRegionOut: function(event,label){
	    				 $(this).css('cursor','default');
	    			 }
		        });
		    }
		      //@code_end
		    </script>
	    </div>
	 </div>
	 
	 <div class="row">
	 <div class="span6">
    	<!-- Timeline of Probes -->
        <h3>Timeline of Probes</h3>
  		<?php if (count($countrycountdates) > 0):?>
        <canvas id="darknetCountryChart" height="300" width="550"></canvas>
        <script type="text/javascript">
        $(document).ready(function(){
            var data = {
                type: 'line',
                labels : [<?php print "'" . implode("','", $datelabels) . "'";?>],
                datasets : [ 
<?php 
                        $colors = array("220,220,220", 
                                        "151,187,205", 
                                        "121,187,105", 
                                        "131,187,205", 
                                        "179,107,205", 
                                        "101,27,205", 
                                        "251,207,205", 
                                        "51,150,205", 
                                        "101,113,205", 
                                        "205,190,205", 
                                        "63,135,205");
                        $x = 0;
                        foreach ($countrycountdates as $country_code=>$countarray) {
                        	print "\t\t\t\t\t{\n";
                            print "\t\t\t\t\t\tlabel: \"" . $country_code . "\",\n";
                            //print "\t\t\t\t\t\ttitle: \"" . $country_code . "\",\n";
                            //print "\t\t\t\t\t\tfillColor : \"rgba(".$colors[$x].",0.2)\",\n";
                            //print "\t\t\t\t\t\tstrokeColor : \"rgba(".$colors[$x].",1)\",\n";
                            //print "\t\t\t\t\t\tpointColor : \"rgba(".$colors[$x].",1)\",\n";
                            //print "\t\t\t\t\t\tpointStrokeColor: \"#fff\",\n";
                            //print "\t\t\t\t\t\tpointHighlightFill: \"#fff\",\n";
                            //print "\t\t\t\t\t\tpointHighlightStroke : \"rgba(".$colors[$x].",1)\",\n";
                            print "\t\t\t\t\t\tdata : [" . implode(",", $countarray) . "]\n";
                            print "\t\t\t\t\t},\n";
                            $x++;
                        }
?>
                ],
            };
            /**var options = {
            	bezierCurve: true,
                multiTooltipTemplate: "<%= datasetLabel%> - <%= value %>",
            };*/
            var myDarknetCountryChart = new Chart($("#darknetCountryChart"), {
                type: 'bar',
                data: data,
            });
        });
        </script>
		<?php else:?>
			No probes tracked.
		<?php endif; ?>
    </div>
    </div>
	
</div>

<div class="tab-pane" id="honeypots">
	<div class="row">
	    <div class="span6">
	   		<!-- Kojoney Login Attempts Map -->
	    	<h3>SSH Honeypot Login Attempts in Last 7 Days</h3>
			<div id="kojoney-map-counts" class="hidden"><?php echo $kojoneymapcounts ?></div>
			<div id="kojoney-worldmap" height="300" width="550" style="height:300px;width:500px;"></div>
			<script type="text/javascript">
			var data = $.parseJSON($('#kojoney-map-counts').text());
			var markers = [];
			var markerValues = [];
			for (iso in data){
				if (latlong.hasOwnProperty(iso)) {
					loc = [latlong[iso]["latitude"],latlong[iso]["longitude"]];
					val = data[iso]
					country = latlong[iso]["name"];
					markers.push({latLng:loc,value:val,name:country,code:iso});
					markerValues.push(val);
				}
				else {
					console.log("Odd, no entry for country " + iso);
				}
			}

			function initializeKojoneyMap() {
				
				$('#kojoney-worldmap').vectorMap({
					map: 'world_mill_en',
					series:{
						markers: [{
					        attribute: 'r',
					        scale: [5, 15],
					        values: markerValues,
					      }]
					},
					 markers:markers,
					 scaleColors: ['#C8EEFF', '#0071A4'],
					 markerStyle: {
						 initial: {
							 fill: '#FF0F00',
							 stroke: '#FF0F00'
						 }
					 },
					 regionStyle: {
					      initial: {
					        fill: '#B8E186'
					      },
					 },
					 backgroundColor: '#C8EEFF',
					 onMarkerLabelShow: function(event,label,index){
						 label.html(
								 "<b>" + markers[index]['name'] + "</b><br/>" + 'Distinct IPs attempting login: ' + markers[index]['value']
								 );
					 },
					 onMarkerClick: function(event,index){
						 location.href = "?action=honeypot&country=" + markers[index]['code'];
					 },
					 onMarkerOver: function(event,label){
						 $(this).css('cursor','pointer');
					 },
					 onMarkerOut: function(event,label){
						 $(this).css('cursor','default');
					 }
				});
			}

			</script>
	    </div>
	</div>
</div>

</div> 