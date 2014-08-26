<h3>Articles</h3>
<?php if (isset($message)):?>
	<div id="message" class="alert"><?php echo $message;?></div>
<?php endif;?>
<div class="row">
	<div class="span12 pagination-centered">
		<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Top Topics</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="top-topic" width="900" height="500"></canvas>		
        	</div>
        	<script>
			        $(document).ready(function(){
			            var data = {labels: <?php echo $labels;?>,
			                        datasets: [
			                            {
			                                label: "My First dataset",
			                                fillColor: "#05EDFF",
			                                strokeColor: "#05EDFF",
			                                pointColor: "#05EDFF",
			                                pointStrokeColor: "#fff",
			                                pointHighlightFill: "#fff",
			                                pointHighlightStroke: "rgba(220,220,220,1)",
			                                data: <?php echo $data ?>
			                            }
			                        ]
			            };
			            var options = {
			                multiTooltipTemplate: "<%= datasetLabel%> - <%= value %>",
			                responsive: true,
			                scaleFontColor: "#000",
			                
			            };
			            var myNewChart = new Chart(document.getElementById("top-topic").getContext("2d")).Bar(data, options);    
			        });
				</script>
        </div>
	</div>
</div>
<div class="row">
	<div class="span12">
		<table id="articles-table" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Date</th>
					<th>Title</th>
					<th>Source</th>
					<th>Teaser</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($articles as $article): ?>
					<tr>
						<td><?php echo $article['date_readable']?></td>
						<td><?php echo $article['linked_title']?></td>
						<td><?php echo $article['source_linked']?></td>
						<td><?php echo $article['teaser']?></td>
						<td>
							<a class="btn" title="Edit this item" href="?action=add_edit&object=article&id=<?php echo $article['id'];?>">Edit</a>
							<a href="#deleteModal<?php echo $article['id'];?>" role="button" class="btn" data-toggle="modal">Delete</a>
						</td>
					</tr>
					<div id="deleteModal<?php echo $article['id'];?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
					<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="deleteModalLabel">Confirm delete!</h3>
					</div>
					<div class="modal-body">
					<p><i class="icon-warning-sign"></i> You are about to <em>permanently</em> delete this record.  This cannot be undone.  Please confirm that you wish to proceed.</p>
					</div>
					<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
					<a class="btn btn-danger" href="?action=article_delete&id=<?php echo $article['id'];?>">Confirm delete</a>
					</div>
					</div>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>