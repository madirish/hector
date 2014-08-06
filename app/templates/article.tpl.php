<h3><?php echo $output['title'];?></h3>
<a class="btn" title="Edit this item" href="?action=add_edit&object=Article&id=<?php echo $output['id'];?>">Edit</a>
<p><?php echo $output['date'];?></p>
<p><?php echo $output['link'];?></p>
<div class="well"><?php echo $output['teaser']?></div>
<div class="well"><input type="button" class="btn btn-info" value="Tags:"/> <?php foreach ($output['tags'] as $tag) echo $tag->get_name(); ?></div>
<p><?php echo $output['body'];?></p>