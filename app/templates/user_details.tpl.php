<?php if (isset($message)) {?>
<div id="message"><?php echo $message;?></div>
<?php } ?>
<h2><?php if (isset($header)) echo $header; else echo $object;?> Details</h2>
<p id='explaination'>Add or manage HECTOR user accounts.</p>
<span id="Addnew">
<a class="link" title="Add a new <?php echo $object;?>" href="?action=add_edit&object=<?php echo $object;?>">
<img src="images/window-new.png" title="Add new <?php echo $object;?>"/>
Add New</a>
</span>
<table id="listtable">
<tr>
	<?php
		foreach(array_keys($displays) as $header) {
			echo '<th>' . $header . '</th>' . "\n\t";
		} 
	?>
	<th colspan="2" class="optionscell">Options</th>
</tr>
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
			<td class="editcell"><a title="Edit this item" href="?action=add_edit&object=<?php echo $object;?>&id=<?php echo $item->get_id();?>">Edit</a></td>
			<td class="deletecell"><span class="link" title="Delete this item" onclick="javascript:if (confirm('Are you sure you want to *permanently* delete this record?')) {getPage('?action=delete&object=<?php echo $object;?>&id=<?php echo $item->get_id();?>');}">Delete</span></td>
			</tr>
			<?php
			$x++;
		}
	?>
</table>