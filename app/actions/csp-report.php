<?php
/**
 * Reporting API for HTML 5 Content Security Policy (CSP) violations.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 */
 
$file = fopen(dirname(__FILE__) . '/../logs/csp-report.txt', 'a');
$json = file_get_contents('php://input');

$csp = json_decode($json, true);
if (isset($csp['csp-report'])) {
	foreach ($csp['csp-report'] as $key => $val) {
	    fwrite($file, $key . ': ' . $val . PHP_EOL);
	}
}

fwrite($file, 'End of report.' . PHP_EOL . PHP_EOL);
fclose($file);
?>