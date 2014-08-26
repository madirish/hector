<?php
/**
 * Script for performing Nmap version detection
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * 
 */
 
/**
 * Make sure of the environment, this script is only designed to be run from
 * the command line (CLI)
 */
if(php_sapi_name() == 'cli') {
	/**
	 * Defined vars
	 */
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../../') . '/';	
	
	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Host_group.php');
	require_once($approot . 'lib/class.Host.php');
	require_once($approot . 'lib/class.Collection.php');
	require_once($approot . 'lib/class.Log.php');
	
	/**
	 * Singletons
	 */
	new Config();
	$nmap = $_SESSION['nmap_exec_path'];
	$db = Db::get_instance();
	
	syslog(LOG_INFO, 'nmap_version_scan.php is starting.');
	$host_ips = array();
	$protocols = array('tcp', 'udp');
	foreach ($protocols as $protocol) {
		// Get distinct ports
		$sql = 'select distinct(nmap_result_port_number) as portnum from nmap_result where state_id = 1 AND nmap_result_protocol = "' . $protocol . '"';
		$ports = $db->fetch_object_array($sql);
		foreach ($ports as $portobj) {
			$port = $portobj->portnum;
			$hosts = new Collection('Host', $port, 'get_collection_by_' . $protocol . '_port');
			if (is_array($hosts->members)) {
				foreach ($hosts->members as $host) $host_ips[] = $host->get_ip();
			}
			// Write IP's to a file for NMAP
			$ipfilename = $approot . 'scripts/nmap_version_scan/version-' . $protocol . '-ips.txt';
			$fp = fopen($ipfilename, 'w') or die("Couldn't open $ipfilename");
			foreach ($host_ips as $ip) fwrite($fp, $ip . "\n");
			$xmloutput = $approot . 'scripts/nmap_version_scan/results-' . time() . '.xml';  // Avoid namespace collissions!
			$command = $nmap;
			$command .= ' -sV -PN -oX ' . $xmloutput . ' ';
			if ($protocol = 'udp') $command .= ' -sU ';
			$command .= ' -p ' . strtoupper(substr($protocol, 0, 1)) . ':' . $port . ' ';
			$command .= ' -T4 -iL ' . $ipfilename;
			syslog(LOG_INFO,"nmap_version_scan.php process executing the command: " . $command);
			shell_exec($command);
			syslog(LOG_INFO,"nmap_version_scan.php process the command: " . $command . " completed!");
			// Process the XML results
			system('/usr/bin/php ' . $approot . 'scripts/nmap_scan/nmap_scan_loadfile.php ' . $xmloutput);
			unlink($xmloutput);
		}
	}
	
	syslog(LOG_INFO, 'nmap_version_scan.php complete.');
}
?>