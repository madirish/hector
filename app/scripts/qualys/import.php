<?php
/**
 * This script is an atomic import of the results of a
 * Qualys scan XML output file.
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 * Last modified 26 April, 2016
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
	require_once($approot . 'lib/class.Vuln.php');
	require_once($approot . 'lib/class.Vuln_detail.php');
	require_once($approot . 'lib/class.Risk.php');
	require_once($approot . 'lib/class.Tag.php');
		
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
	syslog(LOG_INFO, 'Qualys XML import.php starting.');
	
	// Make sure we have some functions that may come from nmap_scan
	if (! function_exists("show_help")) {
		/**
		 * This function may not be instantiated if the script is 
		 * called at the command line.
		 * 
		 * @ignore Don't document this duplicate function.
		 */
		function show_help($error) {
			echo "Error from Qualys XML import.php helper script\n";
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
	$qualysscanxml = simplexml_load_file($xmloutput, 'SimpleXMLElement', LIBXML_NOCDATA);

	if (! $qualysscanxml) {
		loggit("Qualys XML import.php process", "There was a problem parsing the XML file $xmloutput!");
	}
	
	$datetime = new DateTime($qualysscanxml->HEADER->KEY[2], new DateTimeZone('America/New_York'));
	
	foreach ($qualysscanxml->IP as $scanresult) {
		$host = new Host();
		$host->set_ip((string)$scanresult["value"]);
		$host->lookup_by_ip();
		// If there's no host go ahead and create it
		if ($host->get_id() < 1) {
			$host->save();
			print_r("Saved host id: " . $host->get_id(). "\n");
		}
		
		foreach ($scanresult->VULNS as $vuln) {
			foreach($vuln->CAT as $category) {
				foreach($category->VULN as $vulnerability) {
					$tags = array();
					$cve_string = (string)$vulnerability["cveid"];
					if(isset($vulnerability->CVE_ID_LIST)) {
						foreach ($vulnerability->CVE_ID_LIST->CVE_ID as $cve) {
							$tag = new Tag();
							$tag->lookup_by_name((string)$cve->ID);
							$tags[] = $tag->get_id();
						}
					}
					$vuln_obj = new Vuln();
					$vuln_obj->lookup_by_name((string)$vulnerability->TITLE);
					if ($vuln_obj->get_id() < 1) {
						$vuln_obj->set_cve($cve_string);
						$vuln_obj->set_name((string)$vulnerability->TITLE);
						$vuln_obj->set_description((string)$vulnerability->DIAGNOSIS . "\n\n" . (string)$vulnerability->CONSEQUENCE . "\n\n" . (string)$vulnerability->SOLUTION);
						$vuln_obj->set_tag_ids($tags);
						$vuln_obj->save();
					}

					$risk = new Risk();
					$severity = intval((string)$vulnerability["severity"]);
					if ($severity == 5) $risk->lookup_by_name('critical');
					elseif ($severity == 4) $risk->lookup_by_name('high');
					elseif ($severity == 3 ) $risk->lookup_by_name('medium');
					elseif ($severity == 2) $risk->lookup_by_name('low');
					else $risk->lookup_by_name('none');
					
					$vuln_detail = new Vuln_detail();
					$vuln_detail->lookup_by_vuln_id_host_id_date($vuln_obj->get_id(), $host->get_id(), $datetime->format('Y-m-d H:i:s'));
					if ($vuln_detail->get_id() == 0) {
						$vuln_detail->set_vuln_id($vuln_obj->get_id());
						$vuln_detail->set_host_id($host->get_id());
						$vuln_detail->set_datetime($datetime->format('Y-m-d H:i:s'));
						$vuln_detail->set_risk_id($risk->get_id());
						$vuln_detail->save();
					}
				}
			}
		}
	}
	$vulncount = count($qualysscanxml->IP);
	loggit("Qualys import.php process", "Qualys import process complete.  $vulncount records added.");
	
}
?>