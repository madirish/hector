<?php
/**
 * This script is an atomic import of an OSSEC alert log
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 */
 
/**
 * Defined vars and ensure we only execute under CLI includes
 */
if(php_sapi_name() == 'cli') {
	
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../../') . '/';	
	
	if(isset($GLOBALS['override_approot'])) {
		$approot = $GLOBALS['override_approot'];
	}

	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Dblog.php');
	require_once($approot . 'lib/class.Host.php');
	require_once($approot . 'lib/class.Tag.php');
	require_once($approot . 'lib/class.Log.php');
	require_once($approot . 'lib/class.Ossec_Alert.php');
	require_once($approot . 'lib/class.Vuln.php');
	require_once($approot . 'lib/class.Vuln_detail.php');
	require_once($approot . 'lib/class.Risk.php');
	require_once($approot . 'lib/class.Tag.php');
		
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
	syslog(LOG_INFO, 'OSSEC alert log import.php starting.');
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	
	// Local variables
	$debug = 0;
	$reading_alert = 0;
	$alert = null;
	
	
	/**
	 * Process the log line by line
	 * 
	 * @param String The line from the log
	 */
	function process_log_line($line) {
		global $reading_alert;
		global $alert;
		/**
		 * ** Alert 1463651767.22664: mail  - syslog,errors,
		 * 2016 May 19 05:56:07 hector->/var/log/messages
		 * Rule: 1002 (level 2) -> 'Unknown problem somewhere in the system.'
		 * May 19 05:56:06 hector HECTOR[5431]: 2016-05-19 05:56:06  ERROR: 127.0.0.1  IP failed to validate at Darknet::set_dst_ip()#011
		 */
		// Start a new alert
		if (substr($line, 0, 8) == '** Alert') {
			// Track that we found one alert so we can save the last one
			$reading_alert = 1;
			if ($alert == null) {
				// Very first alert
				$alert = new Ossec_Alert();
			}
			else {
				// Save the working alert and start a new one
				$alert->save();
				$alert = new Ossec_Alert();
			}
		}
		
		$alert->process_log_line($line);
	}
	
	// Make sure we have some functions that may come from nmap_scan
	if (! function_exists("show_help")) {
		/**
		 * This function may not be instantiated if the script is 
		 * called at the command line.
		 * 
		 * @ignore Don't document this duplicate function.
		 */
		function show_help($error) {
			echo "Error from OSSEC alert log import.php helper script\n";
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
			/** 
			 * We have to go through some annoying backflips here to bootstrap
			 * the test harness
			 */
			global $log;
			global $dblog;
			if (! $log instanceof Log) $log = Log::get_instance();
			if (! $log instanceof Dblog) $dblog = Dblog::get_instance();
			$log->write_message($message);
			$dblog->log($status, $message);
		}
	}
	
	// Check to make sure arguments are present (note there is no $argc in test suite call)
	if (isset($argc)) {
		if ($argc < 2) show_help("Too few arguments!  You tried:\n " . implode(' ', $argv));
		$alert_file = $argv[1];
	
		if (! $handle = @fopen($alert_file, "r") ) {
			loggit("OSSEC alert log import.php process", "There was a problem opening the OSSEC alert log.");
		}
		else {
			while (($line = fgets($handle, 4096)) !== false) {
				process_log_line($line);
			}
			if (!feof($handle)) {
				echo "Error: unexpected fgets() fail\n";
			}
			// Save the last alert
			if ($reading_alert > 0) {
				$alert->save();
			}
			
			fclose($handle);
		}
	}
	
	loggit("OSSEC alert log import.php process", "OSSEC alert log import process complete.");
	
}
?>