<?php

/**
 *   
 * This script uses phantomjs to take screenshots
 * of websites based on the data from the
 * database's 'url' table. URLs are first pulled
 * from the database. Then each URL's HTTP
 * response header is checked. If the response
 * code is good the URL is passed to phantomjs
 * which saves a screenshot to the file system
 * and the file name is added to the URL table in
 * the database.
 * 
 *   
 * Example usage:
 * 
 * $ php screenshot_scan.php
 * 
 * This script is run from scan_cron.php 
 * 
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @package HECTOR
 * 
 * Last modified July 18, 2013
 */
 
if(php_sapi_name() == 'cli') {
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../') . '/';	
}

/**
 * Neccesary includes
 */
require_once($approot . 'lib/class.Config.php');
require_once($approot . 'lib/class.Dblog.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Host_group.php');
require_once($approot . 'lib/class.Log.php');
require_once($approot . 'lib/class.Scan_type.php');

// Make sure of the environment
global $add_edit;
if(php_sapi_name() != 'cli') {
	$is_executable[] = array('screenshot_scan.php' => 'Screenshot scan');
		global $javascripts;
	$javascripts[] = <<<EOT
	<script type="text/javascript">
		function screenshot_scan_display() {
			var screenshotHTML = "Screenshot";
			document.getElementById("specs").innerHTML = screenshotHTML;
		}
	</script>
EOT;
	$onselects['screenshot_scan.php'] = 'screenshot_scan_display()';
}
else {	
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');	
	syslog(LOG_INFO, 'screenshot_scan.php starting.');		
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	$dblog->log("screenshot_scan.php status", "screenshot_scan.php invoked.");
	$log->write_message("screenshot_scan.php invoked.");
	normalize_database();
	populate_database();
	$sql = "select url_url from url";
	$results= $db->fetch_object_array($sql);	
	foreach($results as $result) {
		$output='';
		$url=$result->url_url;
		if (!(substr($url,0,4)=='http')) {$url = 'http://' . $url;}
		$file_name = str_replace(array('/','.',':'),'_',$result->url_url) . "_" . time() .".png";
		$code = get_headers($url);
		if ($code) {
			$code = substr($code[0], 9, 3);
			$dblog->log("screenshot_scan.php process", "The url: " . $url . " gave response code " . $code . ".");
			$log->write_message("The url: " . $url . " gave response code " . $code . ".");
		}
		else {
			$dblog->log("screenshot_scan.php process", "The url: " . $url . " is down.");
			$log->write_message("The url: " . $url . " is down.");
		}
		//conditional can be changed to accept certain response codes (200,301,etc...)
		if($code) {
			$command = "phantomjs /opt/hector/app/scripts/snapshot.js '" . $url . "' '" . $file_name . "'";
			$output = trim(shell_exec($command));
			$dblog->log("screenshot_scan.php process", "The command: " . $command . " completed! Output: " . $output);
			$log->write_message("The command: " . $command . " completed! Output: " . $output);
			if (strstr($output, "Status:  success")) {
				$db->iud_sql(array('update url set url_screenshot = \'?s\' where url_url= \'?s\'', $file_name, $result->url_url));
			}
		}
	}
	// Shut down nicely
	$dblog->log("screenshot_scan.php status", "screenshot scan complete.");
	$log->write_message("screenshot scan complete.");
	$db->close();
	syslog(LOG_INFO, 'screenshot_scan.php complete.');
}
function get_hosts_by_port($hasport) {
	$filter = '';
	$hosts = array();
	$host_ids = array();
	// Restrict machines based on port specifications
	if ($hasport != null) {
		$filter .= ' AND nsr.state_id=1 AND nsr.nmap_scan_result_port_number in (' . mysql_real_escape_string($hasport) . ')';
		$prevscan = new Collection('Nmap_scan_result', $filter);
		if (isset($prevscan->members) && is_array($prevscan->members)) {
			// rebuild the $hosts and $host_ids arrays
			foreach($prevscan->members as $seenhosts) {
				if (array_search($seenhosts->get_host_id(), $host_ids) === FALSE)
				$hosts_ids[] = $seenhosts->get_host_id();
				$tmphost = new Host($seenhosts->get_host_id());
				$hosts[$tmphost->get_ip()] = $tmphost;
			}
		}
	}
	return $hosts;
}

function normalize_database() {
	$blacklist = array('.au','.doc','.docx','.gz','.pdf','.ppt');
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	$sql = "select url_url from url";
	$results = $db->fetch_object_array($sql);
	foreach($results as $result) {
		$url = $result->url_url;
		$extension = strrchr($url, '.');
		if (in_array($extension, $blacklist)) {
			$db->iud_sql(array('delete from url where url_url=\'?s\'', $url));
			$dblog->log("screenshot_scan.php process", $url . "removed from database due to extension \'" . $extension . "\'");
			$log->write_message("screeshot scan: " . $url . "removed from database due to extension \'" . $extension . "\'");
		}
	}
}

function populate_database() {
	$hosts = get_hosts_by_port(80);
	foreach ($hosts as $host) {
		$db = Db::get_instance();
		$sql= array('insert ignore into url set host_id=?i, host_ip=INET_ATON(\'?s\'), url_url=\'?s\'' ,
			$host->get_id() ,
			$host->get_ip() ,
			'http://' . $host->get_ip()
		);
		$db->iud_sql($sql);
	}
	$hosts = get_hosts_by_port(443);
	foreach ($hosts as $host) {
		$db = Db::get_instance();
		$sql = array('insert ignore into url set host_id=?i, host_ip=INET_ATON(\'?s\'), url_url=\'?s\'' ,
			$host->get_id() ,
			$host->get_ip() ,
			'https://' . $host->get_ip()
		);
		$db->iud_sql($sql);
	}			
}
?>
