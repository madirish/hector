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
	require_once($approot . 'lib/class.Vulnscan.php');
	require_once($approot . 'lib/class.Risk.php');
	require_once($approot . 'lib/class.Tag.php');
		
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
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
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
		
	$scanname = "Intensive Scan of Public IP's (Target: External IP's) (Profile: Standard Scan plus OS and Complete Vuln Scan)";
	$vulnscan1 = new Vulnscan($scanname, '2016-04-19 13:15:09');
	$vulnscan2 = new Vulnscan($scanname, '2016-05-03 12:20:14');
	print "\n";
	print count($vulnscan1->get_vuln_detail_ids()) . "\n";
	print count($vulnscan2->get_vuln_detail_ids()) . "\n";
	$vulnarray = $vulnscan2->delta($vulnscan1);
	print "\nAfter delta:\n";
	print count($vulnarray[0]->get_vuln_detail_ids()) . "\n";
	print count($vulnarray[1]->get_vuln_detail_ids()) . "\n";
	
	
}	
	