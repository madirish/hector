<?php
/**
 * Script for performing route scans and imports based on
 * specifications in the database.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Move the alerts e-mail into the nmap_scan.php script since it's specific to those findings
 * Last modified: Feb 20, 2013
 */


/**
 * Make sure of the environment
 */ 
if(php_sapi_name() == 'cli') {
	/**
	 * Defined vars
	 */
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../') . '/';
    
    log_scan_cron('Starting RSS import with /usr/bin/python ' . $approot . 'scripts/rssimport.py');
	system('/usr/bin/python ' . $approot . 'scripts/rssimport.py');
	
	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
    require_once($approot . 'lib/class.Dblog.php');
	require_once($approot . 'lib/class.Host.php');
    require_once($approot . 'lib/class.Log.php');
	require_once($approot . 'lib/class.Alert.php');
	require_once($approot . 'scripts/mail_alerts.php');
	$scriptrun = 1;
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
    $log = Log::get_instance();
    $dblog = Dblog::get_instance();
	
	/**
	 * Determine which scans we need to run
	 */
	$match_time = 'and scan_daily = 1 OR ' . 
				'scan_dayofweek = date_format(now(),\'%w\')+1 OR ' . 
				'scan_dayofmonth = date_format(now(), \'%d\')+1 OR ' . 
				'scan_dayofyear = date_format(now(), \'%j\')+1';
	$scans = new Collection('Scan', $match_time);
	log_scan_cron('scan_cron.php starting');
	if (isset($scans->members) && is_array($scans->members)) {
		foreach ($scans->members as $scan) {
			
			// Enumerate the scripts
			$script = $scan->get_type()->get_script();
			// ex: /usr/bin/php /opt/hector/app/scripts/nmap_scan/nmap_scan.php
			$scriptfile = $approot . 'scripts/' . substr($script, 0, -4) . '/' . $script;
			$flags = $scan->get_type()->get_flags();
			// Set flags for group targets
			$flags .= " " . $scan->get_group_flags();
			$alert = new Alert();
			$alert->set_host_id(1);
			// Run the scan
			if (is_file($scriptfile)) {
				log_scan_cron('Invoking: /usr/bin/php ' . $scriptfile . ' ' . $flags);
				$last_line = system('/usr/bin/php ' . $scriptfile . ' ' . $flags, $retval);
				// Log the result
                if ($retval == 0) {
                	log_scan_cron('Success!  scan_cron.php ran ' . $scriptfile . ' ' . $flags . ' [retval was ' . $retval . ']');
                }
                else {
                	log_scan_cron('FAILED!  scan_cron.php ran ' . $scriptfile . ' ' . $flags . ' [retval was ' . $retval . ']');
                }
			}				
			else 
				log_scan_cron('scan_cron.php cannot file the file ' . $scriptfile);
			
			// Alert
			$alert->save();
			$alert->set_string('Scan ' . $scan->get_name() . ' finished successfully!');
			$alert->save();
			log_scan_cron('Scan ' . $scan->get_name() . ' finished successfully!');
		}
	 }
	log_scan_cron('scan_cron.php scans complete.');
	mail_alerts();
	
	// Shut down nicely
	$db->close();
}

function log_scan_cron($message) {
    $log = Log::get_instance();
    $dblog = Dblog::get_instance();
    $log->write_message($message);
    $dblog->log('scan_cron', $message);
    syslog(LOG_INFO, $message);
    // Print a message in case cron e-mails results
    print($message);
}
?>
