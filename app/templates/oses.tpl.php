<h3>Operating Systems</h3>
<?php if (isset($message)):?>
	<div id="message" class="alert"><?php echo $message;?></div>
<?php endif;?>
<div class="row">
	<div class="span12 pagination-centered">
		<div class="panel panel-default">
        	<div class="panel-heading">
        		<h4 class="panel-title">Operating Systems</h4>
        	</div>
        	<div class="panel-body">
        		<canvas id="top-os" width="900" height="500"></canvas>
        		<div class="hidden" id="os-labels"><?php echo $labels;?></div>
        		<div class="hidden" id="os-data"><?php echo $data;?></div>		
        	</div>
        </div>
	</div>
</div>



<div class="row">
	<div class="span12">
		<table id="oses-table" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>IP</th>
					<th>Hostname</th>
					<th>OS</th>
					<th>OS Type</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($hosts_by_os as $host): ?>
					<tr>
						<td><?php echo $host->get_ip()?></td>
						<td><?php echo $host->get_name()?></td>
						<td><?php echo $host->get_os()?></td>
						<td><?php echo $host->get_os_type();?></td>
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