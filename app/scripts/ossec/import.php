<?php
/**
 * This script is an atomic import of an OSSEC alert log
 * 
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 * Last modified 9 September, 2016
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
	
	// Local variables
	$debug = 0;
	$reading_alert = 0;
	$alert = null;
	
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
			fclose($handle);
		}
	}
	
	/**
	 * 
	 * @param unknown $line
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
		print ("Starting process_log_line()\n");
		
		// Start a new alert
		if ($reading_alert > 0  && substr($line, 0, 8) == '** Alert') {
			$alert->save();
			$reading_alert = 0;
		}
		elseif ($reading_alert == 0 && substr($line, 0, 8) == '** Alert') {
			$reading_alert = 1;
			$alert = new Ossec_Alert();
		}
			
		print "Examining: $line\n";
		
		//Process the first line
		if (substr($line, 0, 8) == '** Alert') {
			//Find alert id start and end
			$pattern = '/Alert \d+\.\d+/';
			preg_match($pattern, $line, $matches);
			$alert_id =  $matches[0];
			print("Alert id is: " . $alert_id . "\n\n");
			$alert->set_alert_ossec_id($alert_id);
		}
		
		// Process the source line
		if (preg_match('/^\d{4} [A-Z][a-z]{2} \d\d \d\d:\d\d:\d\d /', $line, $matches)) {
			$alert_date = $matches[0];
			$year = substr($alert_date, 0, 4);
			$month = substr($alert_date, 5, 3);
			$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$month = array_search($month, $months) + 1;
			if (strlen($month) < 2) $month = '0' . $month;
			$day = substr($alert_date, 9, 2);
			$time = substr($alert_date, -9);
			preg_match('/ (\w|\.)+->/', $line, $sources);
			$source = isset($sources[0]) ? substr($sources[0], 1, -2) : 'Source not found';
			preg_match('/->\S+$/', $line, $logs);
			$log = substr($logs[0], 2);
			print("* Date is $year-$month-$day $time\n* Source is $source\n* Log is $log\n");
			$alert->set_alert_date("$year-$month-$day $time");
		}
		
		// Process rule line
		if (substr($line, 0, 6) == 'Rule: ') { print "RULE!\n";
			preg_match('/^Rule: \d+ /', $line, $matches);
			$alert_id = $matches[0];
			$alert = new Ossec_Alert('', $alert_id);
			print "Alert_id is " . $alert->get_id() . "\n";
			if ($alert->get_id() < 1) {
				// New alert
				$pattern = '/level \d+\)/';
				preg_match($pattern, $line, $matches);
				$alert_level = $matches[0];
				print("Alert level is " . $alert_level);
				// Get alert text
			}
		}
		
		// Process source ip line
		if (substr($line, 0, 7) == 'Src IP:') {
			preg_match('/^Src IP: \d+.\d+.\d+\d+/',$line,$srcips);
			$srcip = isset($srcips[0]) ? substr($srcips[0], 8) : 'No source ip found';
			print("* Source IP is $srcip\n");
			$alert->set_rule_src_ip($srcip);
		}
		
		// Process destination ip line
		if (substr($line, 0, 7) == 'Dst IP:') {
			preg_match('/^Dst IP: \d+.\d+.\d+\d+/',$line,$dstips);
			$dstip = isset($dstips[0]) ? substr($dstips[0], 8) : 'No source ip found';
			print("* Dest IP is $dstip\n");
		}
		
		// Process user line
		if (substr($line, 0, 5) == 'User:') {
			preg_match('/^User: .*/',$line,$usernames);
			$username = isset($usernames[0]) ? substr($usernames[0], 6) : 'No source ip found';
			print("* User name is $username\n");
			$alert->set_rule_user($username);
		}
		
	}
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	
	
	
	loggit("OSSEC alert log import.php process", "OSSEC alert log import process complete.");
	
}
?>