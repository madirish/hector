<?php 
/**
 * Import an iptables log file 
 * 
 * $dnet_sql = "INSERT INTO darknet SET src_ip = ?, dst_ip = ?, src_port = ?,
        dst_port = ?, proto = ?, received_at = ?, country_code=(select country_code from geoip where ? > start_ip_long AND ? < end_ip_long)";
    my $dnet_sth = $dbi->{dbh}->prepare($dnet_sql) || die("Couldn't prepare darknet insert statement.");
    
    May 17 14:50:29 servername kernel: iptables IN=eth0 OUT= MAC=00:1a:4b:dc:c3:68:88:43:e1:2f:45:1b:08:00 SRC=201.138.46.66 DST=208.88.12.61 LEN=48 TOS=0x00 PREC=0x00 TTL=114 ID=26624 DF PROTO=TCP SPT=4089 DPT=445 WINDOW=65535 RES=0x00 SYN URGP=0
    
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR 
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
	require_once($approot . 'lib/class.Darknet.php');
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Dblog.php');
	require_once($approot . 'lib/class.Log.php');

	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');

	syslog(LOG_INFO, 'iptables import.php starting.');

	// Make sure we have some functions that may come from nmap_scan
	if (! function_exists("show_help")) {
		/**
		 * This function may not be instantiated if the script is
		 * called at the command line.
		 *
		 * @ignore Don't document this duplicate function.
		 */
		function show_help($error) {
			echo "Error from iptables import.php helper script\n";
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

	$logfile = file($argv[1]);
	if ($logfile) {
		/**
		 * Singletons
		 */
		new Config();
		$db = Db::get_instance();
		$dblog = Dblog::get_instance();
		$log = Log::get_instance();
		loggit("iptables import.php process", "Beginning iptables import process");
		
		$record_count = 0;
		
		foreach($logfile as $line) {
			$darknet = new Darknet();
			$darknet->construct_by_syslog_string($line);
			$darknet->save();
			$record_count++;
		}

		loggit("iptables import.php process", "iptables import process complete.  Imported " . $record_count . " records");
		 
	}
	else {
		loggit("iptables import.php process", "There was a problem opening the file " . $argv[1]);
	}
}
?>