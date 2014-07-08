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
    	$sql = 'SELECT DISTINCT(src_ip), country_code from darknet';
        $result = $this->db->fetch_object_array($sql);
        $seenip = array();
        foreach($result as $row) {            
            if (! isset($seenip[$row->src_ip])) {
            	if (isset($retval[$row->country_code])) {
            	$retval[$row->country_code] ++ ;
                }
                else {
                	$retval[$row->country_code] = 1;
                }
                $seenip[$row->src_ip] = 'seen';
            }
        }
        return $retval;
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