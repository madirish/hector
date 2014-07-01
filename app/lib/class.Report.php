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
                "FROM darknet WHERE received_at > DATE_SUB(NOW(), INTERVAL 4 DAY) " .
                "GROUP BY port ORDER BY cnt DESC LIMIT 10";
        return $this->db->fetch_object_array($sql);
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
}