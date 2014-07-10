<h2>Darknet Summary</h2>
<table class="table table-striped table-condensed" id="darknet-probes-summary" name="darknet-probes-summary">
    <thead>
    <tr><th>Source IP</th><th>Protocol</th><th>Destination Port</th><th>Source Port</th><th>Country</th><th>Time</th></tr>
    </thead>
    <tbody>
    <?php
    $x=1;
    foreach ($darknets as $probe) {
        echo "<tr";
        if ($x%2) echo " class='odd'";
        $ip = long2ip($probe->get_src_ip());
        $x++;
        echo "><td><a href='?action=attackerip&ip=" . $ip . "'>" . $ip . "</a></td><td>" . 
                $probe->get_proto() . "</td><td>" .
                $probe->get_dst_port() . "</td><td>" .
                $probe->get_src_port() . "</td><td>" .
                $probe->get_country_code() . "</td><td>" .
                $probe->get_received_at() . "</td></tr>";
    }
    ?>
    </tbody>
</table>


<script type="text/javascript" >
$(document).ready( function () {
    var table = $('#darknet-probes-summary').DataTable({
        "ordering": true
    });
    table.draw();
} );
</script>