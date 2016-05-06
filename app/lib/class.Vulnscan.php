<?php
/**
 * HECTOR - class.Vulnscan.php
 *
 * This file is part of HECTOR.
 *
 * @author Justin C. Klein Keane <justin@madirish.net>
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
require_once('class.Vuln_detail.php');


/**
 * Vulnscan is a class to represent runs of various vulnerability
 * scanners (OpenVAS, Qualys, Nessus, etc.).  Although there is no
 * corresponding database table, these scans are identified through
 * the vulnscan_id string in the vuln_detail table.
 *
 * @access public
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 */
class Vulnscan {

    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;
    
    private $name = null;
    
    private $datetime = null;
    
    private $vuln_detail_ids = array();
    
    private $vuln_details = array();
    
    public function __construct($name, $time='') {
    	$this->db = Db::get_instance();
    	$this->name = $name;
    	$sql = '';
    	if ($time !== '') {
	    	$sql = array(
	    			'select vuln_detail_id, vuln_id, vuln_detail_datetime, vulnscan_id 
	    				from vuln_detail 
	    			  	where vulnscan_id like \'?s\' 
	    					and vuln_detail_datetime = \'?d\'',
	    			$name, $time
	    	);
    	}
    	else {
	    	$sql = array(
	    			'select vuln_detail_id, vuln_id, vuln_detail_datetime, vulnscan_id 
	    				from vuln_detail 
	    			  	where vulnscan_id like \'?s\' 
	    					and vuln_detail_datetime = 
	    					(select max(vuln_detail_datetime) from vuln_detail where vulnscan_id like \'?s\')',
	    			$name
	    	);
    	}
    	$result = $this->db->fetch_object_array($sql);
    	if (is_array($result) && isset($result[0])){ 
    		foreach ($result as $row) {
    			$this->vuln_detail_ids[] = $row->vuln_detail_id;
    			$this->vuln_details[] = new Vuln_detail($row->vuln_detail_id);
    		}
    		$this->datetime = $result[0]->vuln_detail_datetime;
    	}
    }
    
    public function delta($scan) {
    	if (! is_a($scan, 'Vulnscan')) return false;
    	$oldscan = $this;
    	$newscan = $scan;
    	foreach ($newscan->get_vuln_details() as $newdetail) {
    		foreach ($oldscan->get_vuln_details() as $olddetail) {
    			if ($newdetail->get_vuln_id() == $olddetail->get_vuln_id() && $newdetail->get_host_id() == $olddetail->get_host_id() ) {
    				$oldscan->drop_detail($olddetail);
    				$newscan->drop_detail($newdetail);
    			}
    		}
    	}
    	return array($oldscan, $newscan);
    }
    
    public function drop_detail($detail) {
    	$key = array_search($detail, $this->vuln_details);
    	if ($key) {
    		unset($this->vuln_details[$key]);		
    		$idkey = array_search($detail->get_id(), $this->vuln_detail_ids);
    		unset($this->vuln_detail_ids[$idkey]);
    	}
    }
    
    public function get_vuln_details() {
    	return $this->vuln_details;
    }
    
    public function get_vuln_detail_ids() {
    	return $this->vuln_detail_ids;
    }
    
    
}