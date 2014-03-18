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
    
	system('/usr/bin/python ' . $approot . 'scripts/rssimport.py');
	
	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Host.php');
	require_once($approot . 'lib/class.Alert.php');
	require_once($approot . 'scripts/mail_alerts.php');
	$scriptrun = 1;
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	
	/**
	 * Determine which scans we need to run
	 */
	$match_time = 'and scan_daily = 1 OR ' . 
				'scan_dayofweek = date_format(now(),\'%w\')+1 OR ' . 
				'scan_dayofmonth = date_format(now(), \'%d\')+1 OR ' . 
				'scan_dayofyear = date_format(now(), \'%j\')+1';
	$scans = new Collection('Scan', $match_time);
	syslog(LOG_INFO, 'scan_cron.php starting');
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
			$alert->set_string('Scan ' . $scan->get_name() . ' finished successfully!');
			// Run the scan
			if (is_file($scriptfile)) {
				$last_line = system('/usr/bin/php ' . $scriptfile . ' ' . $flags, $retval);
				// Log the result
				syslog(LOG_INFO, 'scan_cron.php ran ' . $scriptfile . ' ' . $flags . ' [retval was ' . $retval . ']');
			}				
			else 
				syslog(LOG_ERROR, 'scan_cron.php cannot file the file ' . $scriptfile);
			
			// Alert
			$alert->save();
		}
	 }
	syslog(LOG_INFO, 'scan_cron.php scans complete.');
	mail_alerts();
	
	// Shut down nicely
	$db->close();
}
?>
