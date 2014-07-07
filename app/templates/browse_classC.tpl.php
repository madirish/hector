<h2>IP's in <?php echo htmlspecialchars($_GET['classC']);?>.x</h2>
<table id="tableClassC" name="tableClassC" class="table table-striped table-bordered">
<thead>
    <tr><th>Hostname</th><th>IP</th><th>Open Ports</th><th>OS</th></tr>
</thead>
<?php
if (isset($hosts) && is_array($hosts)) {
    foreach ($hosts as $host) {
        $name = ($host->get_name() !== '') ? $host->get_name() : $host->get_ip();
        print "<tr><td><a href='?action=host_details&id=" . $host->get_id();
        print "'>".htmlspecialchars($name)."</a></td><td>".htmlspecialchars($host->get_ip())."</td>";
        print "<td>" . $host->get_open_ports() . "</td>";
        print "<td>" . htmlspecialchars($host->get_os()) . "</td>";
        print "</tr>";
    }
}
?>
</table>

<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#tableClassC').DataTable({
        "ordering": true
    });
    table.column('0:visible').order('desc');
    table.draw();
} );
</script>