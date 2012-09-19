<table>
<?php 
foreach ($items as $scan) {
	print '<tr>';
	print '<td>' . $scan->get_name() . '</td>';	
	print '<td>' . $scan->get_friendly_dayofweek() . '</td>';
	print '</tr>';
}
?>
</table>