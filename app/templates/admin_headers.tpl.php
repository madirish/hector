<?php
if (! isset($_GET['ajax'])) include_once($templates . 'header.tpl.php');
?>
<script type="text/javascript" src="js/ajaxFunctions.js"></script>
<script type="text/javascript" src="js/formVerify.js"></script>

<div id="headerlinks">

<ul id="links">
	<li><a href="?action=summary" title="Summary screen with overview statistics">Summary</a></li>
	<li><a href="?action=assets" title="View, Add, and Search the database">Asset Management</a></li>
	<li><a href="?action=reports" title="Custom reports and statistics">Reports</a></li>
	<li><a href="?action=detection" title="Browse malicious attacks and IPs">Intrusion Detection</a></li>
	<li><a href="?action=config" title="HECTOR configuration and user account management">System Configuration</a></li>
	<li><a href="?action=logout" title="End your session">Log out</a></li>
</ul>

</div>

<div id="content">
