<?php
/**
 * Ncrack scan.
 * 
 * For full help on usage see show_ncrack_help() below.  
 * Example usage:
 * 
 * $ php ncrack_scan.php -p=telnet,ssh:24 -g=1,4 
 * 
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 * @todo Remove testing output
 * @todo Filter hosts by open ports
 * @todo Determine correct vuln_id (currently hardcoded to 1)
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
require_once($approot . 'lib/class.Scan_type.php');
	
// Make sure of the environment
global $add_edit;
if(php_sapi_name() != 'cli') {
	$servicelist='';
	if (isset($_GET['id'])) {
		$id = intval($_GET['id']);
		$scan = new Scan_type($id);
		$flags = $scan->get_flags();
		$flags = explode('-', $flags);
		foreach ($flags as $flag) {
			if (substr($flag, 0,1)=='p') $servicelist = substr($flag, 2);
		}
	}
	$is_executable[] = array('ncrack_scan.php' => 'ncrack scan');
	global $javascripts;
	$javascripts .= <<<EOT
	<script type="text/javascript">
		function ncrack_display() {
			var ncrackHTML = "<p>NCRACKNcrack is a high-speed network authentication cracking tool.</p>";
			ncrackHTML += "<p> Protocols supported include RDP, SSH, http(s), SMB, pop3(s), VNC, FTP, and telnet.</p>";
			ncrackHTML += "Services to scan (comma delimited): <input type='text' id='servicelist' onBlur='updateServices()' value='$servicelist'/>";
			ncrackHTML += "<small> Ex. 'telnet,SSH:24'</small>"
			document.getElementById("specs").innerHTML = ncrackHTML;
		}
		
		function updateServices() {
			var ports =  document.getElementById("servicelist").value
			if (ports=='') document.getElementById("flags").value = '';
			else document.getElementById("flags").value = "-p=" + ports;
		}
		// Fire this up as it's the default
		ncrack_display();
	</script>
EOT;
	$onselects['ncrack_scan.php'] = 'ncrack_display()';
}
else {
	// Set high mem limit to prevent resource exhaustion
	ini_set('memory_limit', '512M');
	
	syslog(LOG_INFO, 'ncrack_scan.php starting.');
	
	$scriptrun = 1;
	
	/**
	 * Singletons
	 */
	new Config();
	$db = Db::get_instance();
	$dblog = Dblog::get_instance();
	$log = Log::get_instance();
	$ncrack = $_SESSION['ncrack_exec_path'];
	if (! is_executable($ncrack)) {
		ncrack_loggit("ncrack_scan.php status", "Couldn't locate ncrack executable from config.ini, quitting.");
		die("Can't find ncrack executable at $ncrack.  Check your config.ini.\n");
	}
	ncrack_loggit("ncrack_scan.php status", "ncrack_scan.php invoked.");
	
	//set the defaults
	$groups = null;
	$services = null;
	
	/**
	 * This will be an associative array of the form
	 * host_ip => Host object
	 */
	$hosts = array();
	
	/**
	 * This is an array of the 10 most common usernames from kojoney2.
	 * These usernames will be used as the username list for the scan.
	 */
	$usernames = array();
	
	/**
	 * This is an array of the 10 most common passwords from kojoney2.
	 * These passwords will be used as the password list for the scan.
	 */
	$passwords = array();	 
	
	// Parse through the command line arguments
	foreach ($argv as $arg) {
		if (substr($arg, -15) == 'ncrack_scan.php') continue;
		$flag = substr(strtolower($arg),0,2);
		if ($flag == '-g') $groups = substr($arg,strpos($arg,'=')+1);
		if ($flag == '-p') $services = substr($arg,strpos($arg,'=')+1);
	}
	
	// Determine host groups
	if ($groups != NULL) {
		$groups = mysql_real_escape_string($groups);
		$host_groups = new Collection('Host_group', 'AND host_group_id IN(' . $groups .')');
		if (isset($host_groups->members) && is_array($host_groups->members)) {
			foreach($host_groups->members as $host_group) {
				foreach ($host_group->get_host_ids() as $host_id) {
					$newhost = new Host($host_id);
					$hosts[$newhost->get_ip()] = $newhost;
				}
			}
		}
	}
	else {
		// just grab all the hosts
		$allhosts = new Collection('Host');
		if (isset($allhosts->members) && is_array($allhosts->members)) {
			foreach ($allhosts->members as $newhost) {
				$hosts[$newhost->get_ip()] = $newhost;
			}
		}
	}
	$sql = 'select ' . 
				'distinct(username) as uname, ' .
				'count(id) as ucount ' .
			'from koj_login_attempt ' . 
			'group by username ' .
			'order by ucount desc limit 10';
	$results= $db->fetch_object_array($sql);
	foreach($results as $result) $usernames[] = $result->uname;
	$sql = 'select ' .
				'distinct(password) as passwd, ' .
				'count(id) as pcount ' .
			'from koj_login_attempt ' .
			'group by passwd ' .
			'order by pcount desc limit 10';
	$results= $db->fetch_object_array($sql);
	foreach($results as $result) $passwords[] = $result->passwd;
	$command = $ncrack;
	$command .= ' -p ' . $services;
	$command .= ' --user ' . implode(',', $usernames);
	$command .= ' --pass ' . implode(',', $passwords);
	$command .= ' ' . implode(' ', array_keys($hosts));
	print $command . "\r\n\r\n";
	$output = shell_exec($command);
	print_r($output);
	if(preg_match_all("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) (\d+\/\w+) (\w+)\: \'(.+)\' \'(.+)\'/", $output, $matches, PREG_SET_ORDER)) {
		print_r($matches);
		foreach($matches as $match) {
			$text = 'Easily guessed credentials for service: ' . $match[3] . 
					' on port: ' . $match[2] . 
					' with credentials: (' . $match[4] . ':' . $match[5] . ')';
			$sql = array(
					'insert into vuln_detail set '.
						'vuln_id=?i, ' .
						'vuln_detail_text=\'?s\', '.
						'host_id=?i',
					1,
					$text,
					$hosts[$match[1]]->get_id()
					);
			$db->iud_sql($sql);
		}
	}
}

function ncrack_loggit($status, $message) {
	global $log;
	global $dblog;
	$log->write_message($message);
	$dblog->log($status, $message);
}

function show_ncrack_help($error) {
	echo "Usage: ncrack_scan.php [arguments=params]\n";
  	echo $error;
	echo "\n\n";
	echo "Arguments:\n";
	echo "-g\tHost groups id's to scan\n";
	echo "-p\tService(s) to scan\n";
	echo "\n\nExample Usage:\n";
	echo '$ php ncrack_scan.php -p=telnet,ssh:24 -g=1,4' . "\n";
	echo "Would scan for hosts in the 'web servers' and 'critical hosts' groups (id 1 & 4) \n";
	echo "for weak credentials in telnet and ssh on port 24 and store the results in the database.\n\n";
	//exit;
}
?>