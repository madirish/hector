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
 * Last modified February 2, 2012
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
	
	// This OSSC dependent query is deprecated in favor of rsyslog->MySQL darknet logging 
	/*$sql = 'SELECT rule_src_ip, ' .
		'SUBSTRING(a.rule_log, LOCATE("DPT=", a.rule_log), LOCATE(" WINDOW=", a.rule_log) - LOCATE("DPT=", a.rule_log)) AS portnumber ' .  
		'FROM ossec_alerts a, ossec_rules r ' .
		'WHERE a.alert_date > DATE_SUB(CURDATE(), INTERVAL 1 DAY) ' .
			//'AND rule_id = 182 ' .
			'AND a.rule_src_ip != \'128.91.234.47\' ' .
			'AND a.host_id = 31 ' .
			'AND a.rule_id = r.rule_id ' .
			'AND r.rule_number = 104500 ' .
		'ORDER BY portnumber';*/
	$sql = 'SELECT src_ip, concat(dst_port, \' \', proto) as portnumber from darknet where received_at > DATE_SUB(CURDATE(), INTERVAL 1 DAY) ORDER BY dst_port';
	$port_result = $db->fetch_object_array($sql) or die(mysql_error());
	
	$portcounts = array();
	foreach($port_result as $scan) {
		$portcounts[$scan->portnumber] = (isset($portcounts[$scan->portnumber])) ? $portcounts[$scan->portnumber] + 1 : 1;
	} 
	arsort($portcounts);
	$db->close();
	
	$output = "The following are a list of ports (excluding SSH (DPT=22)) on our honeypot that were " .
			"probed in the 24 hour span from midnight to 11:59 PM " .
			"yesterday.\n\n";
	$output .= "Port Number\tHit Count\n";
	$output .= "-------------------------\n";
	foreach ($portcounts as $key=>$val) {
		$tabs = (strlen($key) < 8) ? "\t\t" : "\t";
		$output .=  $key . $tabs . $val . "\n";
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

