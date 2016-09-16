<?php
/**
 * This script is to import malware domains from a txt file with one domain per line
 * 
 * @author Josh Bauer <bauerj@mlhs.org>
 * @package HECTOR
 * 
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
	require_once($approot . 'lib/class.Domain.php');
	require_once($approot . 'lib/class.Malware_service.php');
	require_once($approot . 'lib/class.Dblog.php');
	require_once($approot . 'lib/class.Log.php');
		
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
	syslog(LOG_INFO, 'OpenDNS Malware Domains import.php starting.');
	
	// Make sure we have some functions that may come from nmap_scan
	if (! function_exists("show_help")) {
		/**
		 * This function may not be instantiated if the script is 
		 * called at the command line.
		 * 
		 * @ignore Don't document this duplicate function.
		 */
		function show_help($error) {
			echo "Error from OpenDNS Malware import.php helper script\n";
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
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	loggit("OpenDNS Malware Domain import.php process", "OpenDNS Malware Domain import.php process starting.");
	
	$malware_domain_file_path = $argv[1];
	$malware_domain_file = fopen($malware_domain_file_path, 'r');
	if(!$malware_domain_file) {
		loggit("OpenDNS Malware Domain import.php process", "there was a problem opening the file $malware_domain_file_path");
		exit(1);
	}
	//Load or create OpenDNS as the malware identifying service
	$service = new Malware_service();
	$service->lookup_by_name('OpenDNS');
	if (! $service->get_id() > 0) {
		$service->set_name('OpenDNS');
		$service->save();
	}
	//initialize counters
	$domain_records_created = 0;
	$domain_records_updated = 0;
	$domain_records_existed = 0;
	
	while (($line = fgets($malware_domain_file))!== false) {
		$dn = substr($line,0,-1);
		$domain = new Domain();
		$domain->lookup_by_name($dn);
		if ($domain->get_id() == 0) { //domain doesn't exist in database
			$domain->set_name($dn);
			$domain->set_is_malicious(true);
			$domain->set_marked_malicious_datetime(date('y-m-d H:i:s'));
			$domain->set_service($service);
			$domain->save();
			$domain_records_created++;
		} elseif ($domain->get_is_malicious()===false){ //domain exists but is not marked malicious
			$domain->set_is_malicious(true);
			$domain->set_marked_malicious_datetime(date('y-m-d H:i:s'));
			$domain->set_service($service);
			$domain->save();
			$domain_records_updated++;
		} else {           //domain exists and is marked malicious
			$domain_records_existed++;
		}
	}

	loggit("OpenDNS Malware Domain import.php process", "OpenDNS Malware Domain import.php process complete.  $domain_records_created records added. $domain_records_updated records updated. $domain_records_existed records already marked.");
	
}
?>