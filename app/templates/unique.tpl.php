<?php if(isset($message)) echo '<div id="message" class="alert">' . $message . '</div>';?>
<p>
<span id="editspan">
<a class="btn btn-primary" title="Edit this item" href="?action=add_edit&object=<?php echo $object;?>&id=<?php echo $specific->get_id();?>">Edit</a>
</span>
</p>
<?php
	echo $output;	
?>