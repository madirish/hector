<?php
/**
 * HECTOR - class.Report.php
 *
 * This file is part of HECTOR.
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 *  Set up error reporting 
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}


/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('class.Host.php');


/**
 * Report class is used for generating various reports in an 
 * object oriented way.
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
class Report {

    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;
    
    public function __construct() {
    	$this->db = Db::get_instance();
    }
    
    public function darknetSummary() { 
    	// Darknet summary:
        $sql = "SELECT CONCAT(dst_port, '/', proto) AS port, count(id) AS cnt " .
                "FROM darknet " .
                "WHERE received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) " .
                "AND dst_port > 0 " .
                "GROUP BY port " .
                "ORDER BY cnt DESC LIMIT 10";
        return $this->db->fetch_object_array($sql);
    }
    
    public function getDarknetCountryCount() {
        $retval = array();
        $countrycount = array();
    	$sql = 'SELECT count(src_ip) as thecount, country_code ' .
                'FROM darknet WHERE' .
                ' received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) ' .
                ' AND country_code IS NOT NULL ' .
                ' GROUP BY country_code ';
        $result = $this->db->fetch_object_array($sql);
        $seenip = array();
        foreach($result as $row) {  
            $retval[$row->country_code] = $row->thecount;  
        }
        return $retval;
    }
    
    /**
     * Get a count of distinct Kojoney login attempts by country
     * 
     * @access public
     * @return Array an associative array of countries and their login attempt counts
     */
    public function getKojoneyCountryCount(){
    	$retval = array();
    	$countrycount = array();
    	$sql = 'SELECT DISTINCT(ip), country_code from koj_login_attempt WHERE time > DATE_SUB(NOW(), INTERVAL 4 DAY) ';
    	$result = $this->db->fetch_object_array($sql);
    	$seenip = array();
    	foreach ($result as $row){
    		if (! isset($seenip[$row->ip])){
    			if (isset($retval[$row->country_code])){
    				$retval[$row->country_code] ++;
    			}else{
    				$retval[$row->country_code] = 1;
    			}
    			$seenip[$row->ip] = 'seen';
    		}
    	}
    	return $retval;
    }
    
    /**
     * Get a listing of class C networks in which we are 
     * tracking hosts for a given class B input.
     * 
     * @access public
     * @param String A dot notation Class B address, such as 10.0
     * @return Array An array of Class C networks in dot notation such as 10.0.0
     */
    public function getClassCinClassB($classB) {
    	$query = 'select distinct(substring_index(host_ip, \'.\', 3)) as ipclass, count(host_id) as thecount';
        $query .= ' from host where host_ip like \'?s%\' group by ipclass';
        $query = array($query, $classB);
        return $this->db->fetch_object_array($query);
    }
    
    public function get_ossec_alert_count($ip) {
    	$sql = 'select a.alert_date, a.rule_log, r.rule_level from ossec_alert a, ossec_rule r ' .
            'where a.rule_id = r.rule_id and r.rule_level >= 7 AND ' .
            'a.rule_src_ip_numeric = inet_aton(\'' . $ip . '\') order by alert_date DESC';
        $ossec_alerts = $this->db->fetch_object_array($sql);
    }
    
    /**
     * Return the number of commands the IP address executed on a kojoney honeypot
     * 
     * @access public
     * @author Justin Klein Keane <jukeane@sas.upenn.edu>
     * @param String The dot notation IP address
     * @return String The number of commands or 'no'
     */
    public function get_koj_executed_commands($ip) {
        $ip = mysql_real_escape_string($ip);
        $commands = '';
        $sql = 'select count(id) as thecount from koj_executed_command where ip = \'' . $ip . '\'';
        $honeypot_commands = $this->db->fetch_object_array($sql);
        if (isset($honeypot_commands[0])) $commands = $honeypot_commands[0]->thecount;
        if ($commands == '') $commands = 'no';
        return $commands;
    }
    
    public function get_darknet_drops($ip) {
    	$ip = mysql_real_escape_string($ip);
        $sql = 'select inet_ntoa(dst_ip) as dst_ip, src_port, dst_port, proto, received_at from darknet ' .
                'where src_ip = inet_aton(\'' . $ip . '\') order by received_at desc';
        return $this->db->fetch_object_array($sql);
    }
    
    /**
     * Determine how many times an IP has been detected attempting to log into 
     * the honeypot.
     * 
     * @access public
     * @author Justin C. Klien Keane
     * @param String IP address in dot notation
     * @return String The number of logins or "no"
     */
    public function get_honeynet_logins($ip) {
        $login_attempts = '';
        $ip = mysql_real_escape_string($ip);
    	$sql = 'select count(id) as thecount from koj_login_attempt where ip_numeric = inet_aton(\'' . $ip . '\')';
        $honeypot_logins = $this->db->fetch_object_array($sql);
        if (isset($honeypot_logins[0])) $login_attempts = $honeypot_logins[0]->thecount;
        if ($login_attempts == '') $login_attempts = 'no';
        return $login_attempts;
    }
    
    public function getHostCount($appuser) {
        if ($appuser->get_is_admin())
        $sql = "select count(host_id) as hostcount from host";
        else {
            $sql = "SELECT COUNT(h.host_id) AS hostcount FROM host h, " .
                    "user_x_supportgroup x " .
                    "WHERE h.supportgroup_id = x.supportgroup_id" .
                    " AND x.user_id = " . $appuser->get_id();
        }
        $hostcount = $this->db->fetch_object_array($sql);
        $count = $hostcount[0]->hostcount;
        return $count;
    }
    
    public function scanCount() {
    	$sql = 'SELECT COUNT(scan_id) AS thecount FROM scan';
        $retval = $this->db->fetch_object_array($sql);
        return $retval[0]->thecount;
    }
    
    public function scriptCount() {
    	$sql = 'SELECT COUNT(scan_type_id) AS thecount FROM scan_type';
        $retval = $this->db->fetch_object_array($sql);
        return $retval[0]->thecount;
    }
    
    /**
     * Return an array of Class B networks containing hosts that we track
     * 
     * @author Justin C. Klein Kenae <jukeane@sas.upen.edu> 
     * @access public
     * @return Array An array of Class B networks in 192.156 style dot notation
     */
    public function getClassBs() {
    	$query = 'select distinct(substring_index(host_ip, \'.\', 2)) as ipclass, count(host_id) as thecount';
        $query .= ' from host group by ipclass';
        return $this->db->fetch_object_array($query); 
    }
 
    public function topTenPorts($appuser) {
        $port_result = array();
    	// Count of top 10 ports
        $sql = 'SELECT DISTINCT(CONCAT(n.nmap_result_port_number, "/", n.nmap_result_protocol)) AS port_number, '  .
                'COUNT(n.nmap_result_id) AS portcount ' .
                'FROM nmap_result n ';
        if ($appuser->get_is_admin()) {
            $sql .= 'WHERE n.state_id = 1 ' .
                'GROUP BY nmap_result_port_number ' .
                'ORDER BY portcount DESC ' .
                'LIMIT 10 ';
        }
        else {
            $sql .= ", host h, user_x_supportgroup x " .
                    "WHERE n.host_id = h.host_id AND h.supportgroup_id = x.supportgroup_id " .
                    "AND x.user_id = " . $appuser->get_id() . " AND n.state_id = 1 " .
                    "GROUP BY nmap_result_port_number " .
                    "ORDER BY portcount desc " .
                    "LIMIT 10 ";
        }
        $port_result = $this->db->fetch_object_array($sql);
        return $port_result;
    }
    
    public function getTopDarknetCountries() {
        $retval = array();
    	$sql = 'SELECT DISTINCT(country_code), COUNT(id) AS countid ' .
                'FROM darknet ' .
                'WHERE received_at > date_sub(now(), interval 7 day) ' .
                'AND country_code IS NOT NULL ' .
                'GROUP BY country_code ' .
                'ORDER BY countid desc LIMIT 10';
        $top_countries = $this->db->fetch_object_array($sql);
        if (is_array($top_countries)) {
        	foreach ($top_countries as $country) {
        		$retval[] = $country->country_code;
        	}
        }
        return $retval;
    }
    
    public function getProbesByCountryDate($country, $date) {
        $date = strtotime($date);
    	$datemin = date('Y-m-d 00:00:00', $date);
        $datemax = date('Y-m-d 24:59:59', $date);
        $sql = 'SELECT COUNT(id) AS idcount ' .
                'FROM darknet ' .
                'WHERE country_code = "' . mysql_real_escape_string($country) . '" ' .
                'AND received_at >= "' . $datemin . '" ' .
                'AND received_at <= "' . $datemax . '"';
        $count = $this->db->fetch_object_array($sql);
        return $count[0]->idcount;
    }
}