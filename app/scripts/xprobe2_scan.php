<?php

/**
 * 
 * Xprobe2 operating system version detection scans.
 * 
 * For full help on usage see show_xprobe2_help() below.  
 * Example usage:
 * 
 * $ php xproe2_scan.php -g=1,4
 * 
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * 
 * Last modified 30 July, 2013
 */
 
/**
 * Defined vars
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
require_once($approot . 'lib/class.Nmap_result.php');
require_once($approot . 'lib/class.Scan_type.php');
	
// Make sure of the environment
global $add_edit;
if(php_sapi_name() != 'cli') {
	
	$is_executable[] = array('xprobe2_scan.php' => 'xprobe2 scan');
	global $javascripts;
	$javascripts[] = <<<EOT
	<script type="text/javascript">
		function xprobe2_display() {
			var xprobe2HTML = "<p>Xprobe2 uses port data from the database to determine operating systems and update host records.</p>";
			xprobe2HTML += "<p>xprobe2  is  an  active  operating system fingerprinting tool with a different approach to operating system";
			xprobe2HTML += "fingerprinting. xprobe2 relies on fuzzy signature matching, probabilistic guesses, multiple matches  simul-";
			xprobe2HTML += "taneously, and a signature database.</p>";
			xprobe2HTML += "<p>The  operation  of  xprobe2  is  described in a paper titled \"xprobe2 - A ´Fuzzy´ Approach to Remote Active";
			xprobe2HTML += "Operating System Fingerprinting\".</p>";
			document.getElementById("specs").innerHTML = xprobe2HTML;
		}
	</script>
EOT;
	$onselects['xprobe2_scan.php'] = 'xprobe2_display()';
}
else {	
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
	syslog(LOG_INFO, 'xprobe2_scan.php starting.');
	
	$scriptrun = 1;
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	$xprobe2 = $_SESSION['xprobe2_exec_path'];
	if (! is_executable($xprobe2)) {
		xprobe2_loggit("xprobe2_scan.php status", "Couldn't locate xprobe2 executable from config.ini, quitting.");
		die("Can't find xprobe2 executable at $xprobe2.  Check your config.ini.\n");
	}
	xprobe2_loggit("xprobe2_scan.php status", "xprobe2_scan.php invoked.");
	
	// Set defaults
	$groups = null;
	
	/**
	 * This will be an associative array of the form
	 * host_ip => Host object
	 */
	$hosts = array();
	
	/** 
	 * Array of ints for quick reference
	 */
	$host_ids = array();
	
	/**
	 * Get the next id for this scan
	 */
	
	
	// Parse through the command line arguments
	foreach ($argv as $arg) {
		if (substr($arg, -16) == 'xprobe2_scan.php') continue;
		$flag = substr(strtolower($arg),0,2);
		if ($flag == '-g') $groups = substr($arg,strpos($arg,'=')+1);
	}
	
	// Determine host groups
	if ($groups != NULL) {
		$groups = mysql_real_escape_string($groups);
		$host_groups = new Collection('Host_group', 'AND host_group_id IN(' . $groups .')');
		if (isset($host_groups->members) && is_array($host_groups->members)) {
			foreach($host_groups->members as $host_group) {
				foreach ($host_group->get_host_ids() as $host_id) {
					$newhost = new Host($host_id);
					if ($newhost->get_ignore_portscan() < 1) {
						$hosts[$newhost->get_ip()] = $newhost;
						$host_ids[] = $newhost->get_id();
					}
				}
			}
		}
	}
	else {
		// just grab all the hosts
		$allhosts = new Collection('Host');
		if (isset($allhosts->members) && is_array($allhosts->members)) {
			foreach ($allhosts->members as $newhost) {
				if ($newhost->get_ignore_portscan() < 1) {
					$hosts[$newhost->get_ip()] = $newhost;
					$host_ids[] = $newhost->get_id();
				}
			}
		}
	}
	$ports = '7,21,22,23,25,53,80,110,123,137,138,139,1433,5600,10000';
	foreach($hosts as $host) {
		// Scan and log each host
		$portstring = '';
		$scanhost = false;
		$ports = $host->get_ports();
		if (isset($ports) && count($ports) > 0) {
			foreach($ports as $port) {
				if ($port->get_state() == 'open' || $port->get_state() == 'closed') {
					$portstring .= ' -p ' . $port->get_protocol() . ':' . $port->get_port_number() . ':' . $port->get_state();
				}
			}
			$scanhost = true;
		}
		if ($scanhost) {
			$xmloutput = $approot . 'scripts/xprobe2-results-' . time() . '.xml';  // Avoid namespace collissions!
			$command = $xprobe2 . ' -m 1 -o ' . $xmloutput . ' -X -B ' . $portstring . ' ' . $host->get_ip();
			xprobe2_loggit("xprobe2_scan.php process", "Executing the command: " . $command);
			shell_exec($command);
			xprobe2_loggit("xprobe2_scan.php process", "The command: " . $command . " completed!");
			
			// Process the xml output file
			$xprobe2_run = simplexml_load_file($xmloutput);
			$host->set_os(trim(str_replace('"', '', $xprobe2_run->target->os_guess->primary[0])));
			$host->save();
			// Delete the xml output file
			unlink($xmloutput);
		}	
	}

	// Shut down nicely
	xprobe2_loggit("xprobe2_scan.php status", "xprobe2 scan complete.");
	$db->close();
	syslog(LOG_INFO, 'xprobe2_scan.php complete.');
}

function show_xprobe2_help($error) {
	echo "Usage: xprobe2_scan.php [arguments=params]\n";
  echo $error;
	echo "\n\n";
	echo "Arguments:\n";
	echo "-g\tHost groups id's to scan\n";
	echo "\n\nExample Usage:\n";
	echo '$ php xprobe2_scan.php -g=1,4' . "\n";
	echo "Would scan for hosts in the 'web servers' and 'critical hosts' groups (id 1 & 4) \n";
	echo "for operating system versions and store the results in the database.\n\n";
	//exit;
}

function xprobe2_loggit($status, $message) {
	global $log;
	global $dblog;
	$log->write_message($message);
	$dblog->log($status, $message);
}

?>
