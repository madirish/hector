<h2>Class B Networks with Hosts</h2>
<ul class='nav nav-tabs nav-stacked'>
<?php
foreach($class_Bs as $row) {
	print "<!-- row -->";
	print "<li><a href='?action=browse_ip&classB=". $row->ipclass ."'>";
	print $row->ipclass . " <span class='badge badge-info'>" . $row->thecount . " hosts</span></a></li>";
}
?>
</ul>