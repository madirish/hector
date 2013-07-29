<?php
/**
 * 
 * Darknet report
 * 
 * Script for reporting on port probes detected by the 
 * sensor yesterday.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @todo Log the scan_id
 * @package HECTOR
 * 
 * Last modified 29 July 2013
 */
  
  
// Set high mem limit to prevent resource exhaustion
ini_set('memory_limit', '512M');
global $add_edit;
// Make sure of the environment
if(php_sapi_name() == 'cli') {

	/**
	 * Defined vars
	 */
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../') . '/';
	/**
	 * Neccesary includes
	 */
	require_once($approot . 'lib/class.Config.php');
	require_once($approot . 'lib/class.Dblog.php');
	require_once($approot . 'lib/class.Log.php');
	require_once($approot . 'lib/class.Db.php');
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	
	$sql = "SELECT CONCAT(dst_port, '/', proto) AS port, count(id) AS cnt " .
		"FROM darknet WHERE received_at > DATE_SUB(NOW(), INTERVAL 1 DAY) " .
		"GROUP BY port ORDER BY cnt DESC LIMIT 10";
	$port_result = $db->fetch_object_array($sql) or die(mysql_error());
	$db->close();
	
	$output = "The following are a list of ports on the darknet sensor that were " .
			"probed in the 24 hour span from midnight to 11:59 PM " .
			"yesterday.\n\n";
	$output .= "Port Number\tHit Count\n";
	$output .= "-------------------------\n";
	foreach ($port_result as $rpt) {
		$tabs = (strlen($rpt->port) < 8) ? "\t\t" : "\t";
		$output .=  $rpt->port . $tabs . $rpt->cnt . "\n";
	}
	
	$to      =  $_SESSION['alert_email'];
	$subject = 'Port probes observed yesterday';
	$message = $output;
	$headers = 'From: ' . $_SESSION['site_email'] . "\r\n" .
	    'Reply-To: ' . $_SESSION['site_email'] . "\r\n" .
	    'X-Mailer: HECTOR';    
	    
	$footer = "\r\n\r\nYou are receiving this e-mail as part of the nightly cron job.  If you " .
		"feel you are getting these alerts in error or if you have any questions about response " .
		"or remediation please contact " . $_SESSION['site_email'];
		
	$message = $output . $footer;
	mail($to, $subject, $message, $headers);
}
?>