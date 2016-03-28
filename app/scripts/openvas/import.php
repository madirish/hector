<?php
/**
 * This script is an atomic import of the results of an
 * OpenVAS scan.
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 * Last modified 28 March, 2016
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
	
	syslog(LOG_INFO, 'OpenVAS import.php starting.');
	
	// Make sure we have some functions that may come from nmap_scan
	if (! function_exists("show_help")) {
		/**
		 * This function may not be instantiated if the script is 
		 * called at the command line.
		 * 
		 * @ignore Don't document this duplicate function.
		 */
		function show_help($error) {
			echo "Error from OpenVAS import.php helper script\n";
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
	$openvasrun = simplexml_load_file($xmloutput);
	if (! $openvasrun) {
		loggit("OpenVAS import.php process", "There was a problem parsing the XML file $xmloutput!");
	}
	
	$datetime = new DateTime($openvasrun->name, new DateTimeZone('America/New_York'));
	
	foreach ($openvasrun->report->results->result as $scanresult) {
		$host = new Host();
		$host->set_ip($scanresult->host);
		$host->lookup_by_ip();
		// If there's no host go ahead and create it
		if ($host->get_id() < 1) {
			$host->save();
			print_r("Saved host id: " . $host->get_id(). "\n");
		}
		
		$tag = new Tag();
		$tag->lookup_by_name($scanresult->nvt->cve);
		
		$vuln = new Vuln();
		$vuln->lookup_by_name_cve($scanresult->nvt->cve, $scanresult->name);
		if ($vuln->get_id() < 1) {
			$vuln->set_cve($scanresult->nvt->cve);
			$vuln->set_name($scanresult->name);
			$vuln->set_description($scanresult->port . "\n\n" . $scanresult->nvt->tags . "\n\n" . $scanresult->nvt->description);
			$vuln->set_tag_ids(array($tag->get_id()));
			$vuln->save();
		}
		
		$risk = new Risk();
		$cvss = floatval($scanresult->nvt->cvss_base);
		if ($cvss > 9) $risk->lookup_by_name('critical');
		elseif ($cvss > 6 && $cvss <= 9 ) $risk->lookup_by_name('high');
		elseif ($cvss > 3 && $cvss <= 6 ) $risk->lookup_by_name('medium');
		elseif ($cvss > 0 && $cvss <= 3 ) $risk->lookup_by_name('low');
		else $risk->lookup_by_name('none');
		
		$vuln_detail = new Vuln_detail();
		$vuln_detail->set_vuln_id($vuln->get_id());
		$vuln_detail->set_host_id($host->get_id());
		$vuln_detail->set_datetime($datetime->format('Y-m-d H:i:s'));
		$vuln_detail->set_risk_id($risk->get_id());
		$vuln_detail->save();
	}
	$vulncount = count($openvasrun->report->results->result);
	loggit("OpenVAS import.php process", "OpenVAS import process complete.  $vulncount records added.");
	
}
?>