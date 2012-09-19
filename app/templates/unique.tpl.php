<?php if(isset($message)) echo '<div id="message">' . $message . '</div>';?>
<span id="editspan">
<a title="Edit this item" href="?action=add_edit&object=<?php echo $object;?>&id=<?php echo $specific->get_id();?>">Edit</a>
</span>

<?php
	echo $output;	
?>