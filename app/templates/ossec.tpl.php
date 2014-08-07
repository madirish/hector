<h2>OSSEC Hosts</h2>

<ol>
<?php
	if (isset($hosts) && is_array($hosts)) {
		foreach ($hosts as $host) {
			echo '<li>[' . $host->get_ip() . 
				'] ' . $host->get_name();
			if ($host->get_name_linked() != '<a href="?action=details&object=host&id=0"></a>')
				echo ' (' . $host->get_name_linked() . ')'; 
			echo '</li>' . "\n";
		}
	}
?>
</ol>