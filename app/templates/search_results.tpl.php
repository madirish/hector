<div id="content">
<h2>Search Results</h2>
Your search returned <span class="badge"><?php echo count($hosts);?></span> results.

<table id="tableSearchResults" name="tableSearchResults" class="table table-striped table-bordered">
<thead>
    <tr>
        <th>Hostname</th>
        <th>ip</th>
        <th>Sponsor</th>
        <th>Technical</th>
        <th>Notes</th>
    </tr>
</thead>
<tbody>
<?php 
if (is_array($hosts)) {
    foreach ($hosts as $host) {
        echo '<tr><td>' . $host->get_name_linked() . '</td>';
        echo '<td>' . $host->get_ip() . '</td>';
        echo '<td>' . $host->get_sponsor() . '</td>';
        echo '<td>' . $host->get_technical() . '</td>';
        echo '<td>' . $host->get_note() . '</td>';
        echo '</tr>' . "\n";
    }
}
?>
</tbody></table>
<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#tableSearchResults').DataTable({
        "ordering": true
    });
    table.draw();
} );
</script>


</div>
<br/></br>