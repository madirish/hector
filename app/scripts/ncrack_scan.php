<?php
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
	
	$is_executable[] = array('ncrack_scan.php' => 'ncrack scan');
	global $javascripts;
	$javascripts[] = <<<EOT
	<script type="text/javascript">
		function ncrack_display() {
			var ncrackHTML = "<p>NCRACK    Xprobe2 uses port data from the database to determine operating systems and update host records.</p/>";
			ncrackHTML += "<p>xprobe2  is  an  active  operating system fingerprinting tool with a different approach to operating system";
			ncrackHTML += "fingerprinting. xprobe2 relies on fuzzy signature matching, probabilistic guesses, multiple matches  simul-";
			ncrackHTML += "taneously, and a signature database.</p>";
			ncrackHTML += "<p>The  operation  of  xprobe2  is  described in a paper titled \"xprobe2 - A ´Fuzzy´ Approach to Remote Active";
			ncrackHTML += "Operating System Fingerprinting\".</p>";
			document.getElementById("specs").innerHTML = ncrackHTML;
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
	$hosts = array('192.168.56.1' => 1, '192.168.56.101' => 2, '192.168.56.102' => 3, '192.168.56.103' => 4, '192.168.56.104' => 5, '192.168.56.105' => 6, '192.168.56.106' => 7);
	$usernames = array();
	$passwords = array();	
	$sql = 'select distinct(username) as uname, count(id) as ucount from koj_login_attempt group by username order by ucount desc limit 10';
	$results= $db->fetch_object_array($sql);
	foreach($results as $result) $usernames[] = $result->uname;
	$sql = 'select distinct(password) as passwd, count(id) as pcount from koj_login_attempt group by passwd order by pcount desc limit 10';
	$results= $db->fetch_object_array($sql);
	foreach($results as $result) $passwords[] = $result->passwd;
	$command = $ncrack . ' -p telnet --user ' . implode(',', $usernames) . ' --pass ' . implode(',', $passwords) . ' 192.168.56.101-106';
	print $command . "\r\n\r\n";
	$output = shell_exec($command);
	print_r($output);
	if(preg_match_all("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) (\d+\/\w+) (\w+)\: \'(.+)\' \'(.+)\'/", $output, $matches, PREG_SET_ORDER)) {
		print_r($matches);
		foreach($matches as $match) {
			$text = 'Easily guessed credentials for service: ' . $match[3] . ' on port: ' . $match[2] . ' with credentials: (' . $match[4] . ':' . $match[5] . ')';
			$sql = 'insert into vuln_detail set vuln_id=1, vuln_detail_text=\''.$text.'\'';
			$db->iud_sql($sql);
			$vd_id = $db->fetch_object_array('select LAST_INSERT_ID() as id from vuln_detail limit 1');
			print_r($vd_id);
			$sql = array('insert into vuln_x_host set host_id=?i, vuln_detail_id=?i', $hosts[$match[1]], $vd_id[0]->id);
			print_r($sql);
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
?>