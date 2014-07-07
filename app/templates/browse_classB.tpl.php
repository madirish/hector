<h2>Class C Subnets with Hosts in <?php echo htmlspecialchars($_GET['classB']);?>.x.x</h2>
<ul class='nav nav-tabs nav-stacked'>
<?php
foreach($class_C_networks as $row) {
    print "<!-- row -->";
    print "<li><a href='?action=browse_ip&classC=". $row->ipclass ."'>";
    print $row->ipclass . " <span class='badge badge-info'>" . $row->thecount . " hosts</span></a></li>";
}
?>
</ul>