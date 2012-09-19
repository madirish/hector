<script type="text/javascript" src="js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<div id="content">

Your search returned <?php echo count($hosts);?> results.
<hr>
<?php 
if (count($hosts) > 0) {
	echo $content;	
}
?>
</div>