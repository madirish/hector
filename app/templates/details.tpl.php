<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>
<h3><?php if (isset($object_readable)) echo $object_readable; else echo $object;?> Details</h3>
<?php if (isset($explaination)) echo "<p id='explaination'>" . $explaination . "</p>"; ?>
<span id="Addnew">
<p>
<a class="btn btn-small" href="?action=add_edit&object=<?php echo $object;?>"><i class="icon-plus"></i>  Add a new <?php echo $object_readable;?></a>
</p>
</span>
<table id="listtable" class="table table-striped table-bordered">
<thead>
<tr>
	<?php
		foreach(array_keys($displays) as $header) {
			echo '<th>' . $header . '</th>' . "\n\t";
		} 
	?>
	<th colspan="2" class="optionscell">Options</th>
</tr></thead><tbody>
	<?php
		$x=0;
		foreach ($items as $item) {
			echo '<tr';
			if ($x%2) echo ' class="gray"';
			echo '>' . "\n";
			foreach (array_values($displays) as $cell) {
				echo '<td>' . call_user_func(array($item, $cell)) . '</td>' . "\n\t";
			}
			?>
			<td class="editcell"><a class="btn" title="Edit this item" href="?action=add_edit&object=<?php echo $object;?>&id=<?php echo $item->get_id();?>">Edit</a></td>
			<td class="deletecell"><a href="#deleteModal<?php echo $item->get_id();?>" role="button" class="btn" data-toggle="modal">Delete</a></td>
			</tr>
			<?php
			$x++;
		}
	?></tbody>
</table>

<?php foreach ($items as $item) { ?>
<!-- Delete Modal -->
<div id="deleteModal<?php echo $item->get_id();?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="deleteModalLabel">Confirm delete!</h3>
</div>
<div class="modal-body">
<p><i class="icon-warning-sign"></i> You are about to <em>permanently</em> delete this record.  This cannot be undone.  Please confirm that you wish to proceed.</p>
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
<a class="btn btn-danger" href="?action=delete&object=<?php echo $object;?>&id=<?php echo $item->get_id();?>">Confirm delete</a>
</div>
</div>
<?php } ?>