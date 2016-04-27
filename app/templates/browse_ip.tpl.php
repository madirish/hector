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
<h2>Hosts by OS (<?php echo count($hosts_by_os);?>)</h2>
<ul class='nav nav-tabs nav-stacked'>
<?php
foreach($oses as $os=>$count) {
	print "<!-- row -->";
	print "<li><a href='?action=assets&object=search&os=". $os ."'>";
	print $os . " <span class='badge badge-info'>" . $count . " hosts</span></a></li>";
}
?>
</ul>
