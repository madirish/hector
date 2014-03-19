<?php
/**
 * Hydra scan.
 * 
 * For full help on usage see show_hydra_help() below.  
 * Example usage:
 * 
 * $ php hydra_scan.php -p=ssh,vnc -g=1,4
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 * @todo Filter hosts by open ports
 * @todo Determine correct vuln_id (currently hardcoded to 1)
 */
 
/**
 * Defined vars
 */
if(php_sapi_name() == 'cli') {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../../') . '/';  
}


/**
 * Neccesary includes
 */
require_once($approot . 'lib/class.Config.php');
require_once($approot . 'lib/class.Dblog.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Scan_type.php');
require_once($approot . 'lib/class.Vuln.php');
require_once($approot . 'lib/class.Vuln_detail.php');
    
// Make sure of the environment
global $add_edit;
if(php_sapi_name() != 'cli') {
     // Error, we shouldn't use this script from the web interface
}
else {
    // Set high mem limit to prevent resource exhaustion
    ini_set('memory_limit', '512M');
    
    syslog(LOG_INFO, 'hydra_scan.php starting.');
    
    $scriptrun = 1;
    
    /**
     * Singletons
     */
    new Config();
    $db = Db::get_instance();
    $dblog = Dblog::get_instance();
    $log = Log::get_instance();
    $hydra = $_SESSION['hydra_exec_path'];
    if (! is_executable($hydra)) {
        hydra_loggit("hydra_scan.php status", "Couldn't locate hydra executable from config.ini, quitting.");
        die("Can't find hydra executable at $hydra.  Check your config.ini.\n");
    }
    hydra_loggit("hydra_scan.php status", "hydra_scan.php invoked.");
    
    //set the defaults
    $groups = null;
    $services = null;
    
    // Ensure we have a vuln to use for any findings
    $vuln = new Vuln();
    $vuln->lookup_by_name('Weak credentials');
    if ($vuln->get_id() < 1) {
    	$vuln->set_name('Weak credentials');
        $vuln->set_description('Service with weak or easily guessed credentials could fall victim to a brute force attack.');
        $vuln->save();
    }
    
    /**
     * This will be an associative array of the form
     * host_ip => Host object
     */
    $hosts = array(); 
    
    // Parse through the command line arguments
    foreach ($argv as $arg) {
        if (substr($arg, -15) == 'hydra_scan.php') continue;
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
    $fp = fopen($approot . 'scripts/hydra_scan/usernames.txt', 'w');
    foreach($results as $result) fwrite($fp, $result->uname . "\n");
    fclose($fp);
    $sql = 'select ' .
                'distinct(password) as passwd, ' .
                'count(id) as pcount ' .
            'from koj_login_attempt ' .
            'group by passwd ' .
            'order by pcount desc limit 10';
    $results= $db->fetch_object_array($sql);
    $fp = fopen($approot . 'scripts/hydra_scan/passwords.txt', 'w');
    foreach($results as $result) fwrite($fp, $result->passwd . "\n");
    fclose($fp);

    foreach ($hosts as $ip=>$host) {
        $command = $hydra;
        $command .= ' -L ' .  $approot . 'scripts/hydra_scan/usernames.txt '; 
        $command .= ' -P ' .  $approot . 'scripts/hydra_scan/passwords.txt '; 
        $command .= ' ' . $ip;
        $command .= ' ' . $services;
    
        hydra_loggit("hydra_scan.php status", $command);
        //print("Preparing to execute command: \n");
        print($command . "\n");
        exec(escapeshellcmd($command), $output, $retval);
        if ($retval != 0) {
            syslog(LOG_WARNING, 'hydra_scan error!  retval was ' . $retval);
        }
        print_r($output);
        foreach ($output as $line) {
            //print($line . "\n");
            // ex: [22][ssh] host: 10.10.0.23   login: root   password: password
            if(preg_match_all("/\[(\d+)\]\[(\w+)\]\W+host\:\W+(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\W+login:\W+(\w+)\W+password:\W+([[:graph:]]+)/", $line, $matches, PREG_SET_ORDER)) {
                print_r($matches);
                foreach($matches as $match) {
                    $text = 'Easily guessed credentials for service: ' . $match[3] . 
                            ' on port: ' . $match[1] . ' (service ' . $match[2] . ')' .
                            ' with credentials: (' . $match[4] . ':' . $match[5] . ')';
                    $vuln_detail = new Vuln_detail();
                    $vuln_detail->set_vuln_id($vuln->get_id());
                    $vuln_detail->set_text($text);
                    $vuln_detail->set_host_id($hosts[$match[3]]->get_id());
                    $vuln_detail->save();
                    print "Saved\n";
                }
            }
        }
    }
    hydra_loggit("hydra_scan.php status", "hydra_scan.php complete.");
}

function hydra_loggit($status, $message) {
    global $log;
    global $dblog;
    $log->write_message($message);
    $dblog->log($status, $message);
}

function show_hydra_help($error) {
    echo "Usage: hydra_scan.php [arguments=params]\n";
    echo $error;
    echo "\n\n";
    echo "Arguments:\n";
    echo "-g\tHost groups id's to scan\n";
    echo "-p\tService(s) to scan\n";
    echo "\n\nExample Usage:\n";
    echo '$ php hydra_scan.php -p=22,23 -g=1,4 -d=60 -c' . "\n";
    echo "Would scan for hosts in the 'web servers' and 'critical hosts' groups (id 1 & 4) \n";
    echo "for weak credentials in telnet and ssh using a 60 second delay and store the results in the database.\n\n";
    //exit;
}
?>