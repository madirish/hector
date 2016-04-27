<?php
/**
 * This script is an atomic import of the results of an
 * NMAP scan run to produce an output XML file.  When the
 * contents of this script were combined with nmap_scan.php
 * there were often issues in the MySQL database that 
 * required this portion to be re-run.  To that end the
 * commands herein were split out to allow for easy CLI
 * control of this step in the process.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * 
 * Last modified 7 August, 2013
 */
 
/**
 * Defined vars and ensure we only execute under CLI includes
 */
if(php_sapi_name() == 'cli') {
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../../') . '/';	

	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Alert.php');
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Dblog.php');
	require_once($approot . 'lib/class.Host.php');
	require_once($approot . 'lib/class.Tag.php');
	require_once($approot . 'lib/class.Log.php');
	require_once($approot . 'lib/class.Nmap_result.php');
		
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
	syslog(LOG_INFO, 'nmap_scan_loadfile.php starting.');
	
	// Make sure we have some functions that may come from nmap_scan
	if (! function_exists("show_help")) {
		/**
		 * This function may not be instantiated if the script is 
		 * called at the command line.
		 * 
		 * @ignore Don't document this duplicate function.
		 */
		function show_help($error) {
			echo "Error from nmap_scan.php helper script nmap_scan_loadfile.php\n";
			echo $error;
			//exit;
		}
	}
	if (! function_exists("loggit")) {
		/**
		 * This function may not be instantiated if the script is 
		 * called at the command line.
		 * 
		 * @ignore Don't document this duplicate function.
		 */
		function loggit($status, $message) {
			global $log;
			global $dblog;
			$log->write_message($message);
			$dblog->log($status, $message);
		}
	}
	
	// Check to make sure arguments are present
	if ($argc < 2) show_help("Too few arguments!  You tried:\n " . implode(' ', $argv));
	
	$xmloutput = $argv[1];
	
	
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
		
	
	// Load up the XML and parse it 
	$nmaprun = simplexml_load_file($xmloutput);
	if (! $nmaprun) {
		loggit("nmap_scan_loadfile.php process", "There was a problem parsing the XML file $xmloutput!");
	}
	// just grab all the hosts
	/* $allhosts = new Collection('Host');
	if (isset($allhosts->members) && is_array($allhosts->members)) {
		foreach ($allhosts->members as $newhost) {
			$hosts[$newhost->get_ip()] = $newhost;
		}
	} */
			
	foreach($nmaprun->host as $nmaphost) {
		// Sometimes scans take more than 8 hours and the MySQL connection closes
		$db = Db::get_instance();
		// look up the host
		$hostip = (string)$nmaphost->address['addr'];
		$host = new Host();
		$host->set_ip($hostip);
		$host->lookup_by_ip();
		// Update the host information
		if (isset ($nmaphost->os)) {
			if (isset($nmaphost->os->osclass['type'])) $host->set_os_type((string)$nmaphost->os->osclass['type']);
			if (isset($nmaphost->os->osclass['vendor'])) $host->set_os_type((string)$nmaphost->os->osclass['vendor']);
			if (isset($nmaphost->os->osclass['osfamily'])) $host->set_os_type((string)$nmaphost->os->osclass['osfamily']);
			if (isset($nmaphost->os->osmatch['name'])) $host->set_os((string)$nmaphost->os->osmatch['name']);
		}
		
		// Track new results via variables
		$nmap_scans = array();
	
		foreach ($nmaphost->ports->port as $port) {
			$result = new Nmap_result();
			$result->set_host_id($host->get_id());
			$result->set_port_number($port['portid']);
			$result->set_protocol($port['protocol']);
			switch ($port->state['state']) {
				case 'open' : $result->set_state_id(1); break;
				case 'closed' : $result->set_state_id(2); break;
				case 'filtered' : $result->set_state_id(3); break;
				case 'open|filtered' : $result->set_state_id(4); break;
				default: $result->set_state_id(5); break;
			}
			$savehost = false;
			$version_info = $port->service['product'] . " " . $port->service['version'];
			if ($version_info != ' ') {
				$version_tag = new Tag();
				$version_tag->lookup_by_name($version_info);
				$host->add_tag_id($version_tag->get_id());
				$savehost = true;
			}
			if (isset($port->service['devicetype']))  {
				$version_info .= ' ' . $port->service['devicetype'];
				$devicetype_tag = new Tag();
				$devicetype_tag->lookup_by_name($port->service['devicetype']);
				$host->add_tag_id($devicetype_tag->get_id());
				$savehost = true;
			}
			if (isset($port->service['extrainfo'])) {
				$version_info .= ' ' . $port->service['extrainfo'];
				$extrainfo_tag = new Tag();
				$extrainfo_tag->lookup_by_name($port->service['extrainfo']);
				$host->add_tag_id($extrainfo_tag->get_id());
				$savehost = true;
			}
			if (isset($port->service['servicefp'])) {
				$version_info .= ' ' . $port->service['servicefp'];
				$servicefp_tag = new Tag();
				$servicefp_tag->lookup_by_name($port->service['servicefp']);
				$host->add_tag_id($servicefp_tag->get_id());
				$savehost = true;
			}
			if ($savehost) $host->save();
			if ($version_info != ' ') $result->set_service_version($version_info);
			$result->set_service_name($port->service['name']);
			$nmap_scans[] = $result;
		}			
	
		foreach($nmap_scans as $scan) {
			$old_scan_result = new Nmap_result();
			$old_scan_result->lookup_scan($scan->get_host_id(), $scan->get_port_number(), $scan->get_protocol());
			if ($old_scan_result->get_id() > 0) {
				if ($scan->get_state_id() == 1 && $old_scan_result->get_state_id() > 1) {
					require_once($approot . 'lib/class.Alert.php');
					$alert = new Alert();
					$string = "Port " . $scan->get_port_number() . $scan->get_protocol() . " changed from " .
										$old_scan_result->get_state() . " to open on " . $host->get_name();
					$alert->set_host_id($host->get_id());
					$alert->set_string($string);
					$alert->save(); 
				}
				$old_scan_result->delete();
			}
			// Set the scan_id, equivelent to the Unix timestamp
			$scan->set_scan_id($nmaphost['starttime']);
			
			// record the new results
			if ($scan->save() === FALSE) {
				loggit("nmap_scan_loadfile.php process", "There was an error saving scan for " . 
						"port " . $scan->get_port_number() . " on host " . $scan->get_host_id());
			}
		}
	}	
}
?>