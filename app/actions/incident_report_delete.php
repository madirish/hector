<?php
/**
 * Delete an incident report
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 */
 
/**
 * Necessary includes
 */
require_once($approot . 'lib/class.Incident.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($incident = new Incident($id)) {
    $incident->delete();
    ?>
<script type="text/javascript">location.href='?action=incident_reports';</script>
    <?php
}
?>