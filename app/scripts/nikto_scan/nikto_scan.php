<?php
/**
 * Script for performing Nikto scans based on
 * specifications in the database.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @todo Log the scan_id
 * @package HECTOR
 * 
 * Last modified Feb 23, 2011  
 */
 
/**
 * Make sure of the environment
 */
global $add_edit;
if(php_sapi_name() != 'cli') {
	// Error, we shouldn't use this script from the web interface
}
else{
	/**
	 * Defined vars
	 */
	if(php_sapi_name() == 'cli') {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../../') . '/';	
	}
	
	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Host_group.php');
	require_once($approot . 'lib/class.Host.php');
	require_once($approot . 'lib/class.Log.php');
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	
	syslog(LOG_INFO, 'nikto_scan.php is starting.');
	
	$count = 0;
	// Get a list of all the port 80 machines
	$webservers = new Collection('Host','80','get_collection_by_port','');
	$approot = $approot . 'scripts/nikto-2.1.4/';
	if (isset($webservers->members) && is_array($webservers->members)) {
		foreach ($webservers->members as $host) {
			$cmd = '/usr/bin/perl ' . $approot . 'nikto.pl -config ' . $approot . 'nikto.conf -h ' . $host->get_ip() . ' -Plugins @@NONE';
			$output = shell_exec($cmd);
			if (! strpos($output, 'No web server found')) { 
				$start = strpos($output, ':80') + 4;
				$end = strpos($output, '-------------', $start);
				$len = $end - $start;
				$server = trim(substr($output, $start, $len));
				$sql = array('update nmap_result set service_version =\'?s\' where port_number=80 and host_id=?i and state_id=1',
					$server,
					$host->get_id()
				);
				$db->iud_sql($sql);
				$count++;
				}
		}
	}
	//echo "Updated $count web server signatures with nikto_scan.php.";
	syslog(LOG_INFO, 'nikto_scan.php complete! ' . $count . ' host webserver signatures updated.');
}
?>