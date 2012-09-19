<?php

/**
 * Script for performing NMAP scans of machines where
 * scan results are more than X months old (to keep
 * the database information current).
 * 
 * Builds a temporary host_group then passes this as
 * an argument to nmap_scan.php.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * 
 * Last modified June 17, 2011  
 */
syslog(LOG_INFO, 'Nmap_rescan_old.php beginning.');
 
$months_old = 1;

// Make sure of the environment
if(php_sapi_name() != 'cli' && ! isset($add_edit)) {
	die('This script (nmap_rescan_old.php) meant to be run from the CLI.');
}
if (! isset($add_edit)) {
	/**
	 * Defined vars
	 */
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../') . '/';
	
	// Path to the nmap_scan.php script
	$nmap_scan = $approot . 'scripts/nmap_scan.php';
	
	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Host_group.php');
	require_once($approot . 'lib/class.Host.php');
	require_once($approot . 'lib/class.Nmap_scan_result.php');
	require_once($approot . 'lib/class.Log.php');
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$log = Log::get_instance();
	$php = $_SESSION['php_exec_path'];
	$log->write_message("Nmap rescan old starting.");
	
	$hostgroup = new Host_group();
	$hostgroup->set_name('old_hosts');
	$hostgroup->save();
	
	$sql = 'select distinct(host_id) from nmap_scan_result ' .
			'where ' .
			'scan_result_timestamp < date_sub(now(), INTERVAL ' . $months_old .' MONTH) ' .
			'AND state_id = 1 ORDER BY nmap_scan_result_port_number';
	$old_hosts = array();
	$result = $db->fetch_object_array($sql);
	if (is_array($result)) {
		foreach($result as $record) $old_hosts[] = $record->host_id;
	}
	
	//Restrict the ports so we only rescan old data
	$sql = 'select distinct(nmap_scan_result_port_number) from nmap_scan_result ' .
			'where ' .
			'scan_result_timestamp < date_sub(now(), INTERVAL ' . $months_old .' MONTH) ' .
			'AND state_id = 1 ORDER BY nmap_scan_result_port_number';
	$ports = array();
	$result = $db->fetch_object_array($sql);
	if (is_array($result)) {
		foreach($result as $record) $ports[] = $record->nmap_scan_result_port_number;
	} 
	$ports = implode(',', $ports);
	
	// Add all the old hosts to the hostgroup (for nmap_scan.php)
	if (count($old_hosts) > 0) {
		foreach($old_hosts as $host) {
			$hostgroup->add_host_to_group($host);
		}
	}
	
	$command = $php . ' ' . $nmap_scan . ' -a -v -p=' . $ports . ' -g=' . $hostgroup->get_id();
	$log->write_message("Running: " . $command);
	shell_exec($command);
	//echo $command;
	
	// Clean up the temporary group
	$hostgroup->delete();
	
	// Shut down nicely
	$db->close();
	syslog(LOG_INFO, 'Nmap_rescan_old.php complete.');
}
?>
