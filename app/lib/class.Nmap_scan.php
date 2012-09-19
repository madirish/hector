<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Nmap_scan.php
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * 
 * Last modified February 1, 2012
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/**
 * Nmap_scan is really just the index for Nmap_scan_result opbjects
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Nmap_scan extends Maleable_Object implements Maleable_Object_Interface {

	// --- ATTRIBUTES ---
	
	/**
	 * Unique id
	 *
	 * @access private
	 * @var int
	 */
	protected $id = null;
	
	/**
	 * Date/time of the scan
	 *
	 * @access private
	 * @var int
	 */
	private $datetime = null;


	/**
	 * Create a new instance
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  int id
	 * @return void
	 */
	public function __construct($id = '') {
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM nmap_scan WHERE nmap_scan_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->nmap_scan_id;
			$this->datetime = $result[0]->nmap_scan_datetime;
		}
		else {
			$sql='INSERT INTO nmap_scan SET nmap_scan_datetime=NOW()';
			$this->db->iud_sql($sql);
			$sql='SELECT LAST_INSERT_ID() AS lastid FROM nmap_scan';
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])) $this->id = $result[0]->lastid;
		}
	}


	/**
	 * Delete the record from the database
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return void
	 */
	public function delete() {
		if ($this->id > 0 ) {
		// Delete an existing record
    	$sql = array(
    		'DELETE FROM nmap_scan WHERE nmap_scan_id = \'?i\'',
    		$this->get_id()
    	);
    	$this->db->iud_sql($sql);
		}
	}
	
	public function get_add_alter_form() {
		// Not yet implemented.
	}

	/**
	 *  This function directly supports the Collection class.
	 *
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT nmap_scan_id from nmap_scan where nmap_scan_id > 0';
		if ($filter != '' && is_array($filter))  {
			$sql .= ' ' . array_shift($filter);
			$sql = $this->db->parse_query(array($sql, $filter));
		}
		if ($filter != '' && ! is_array($filter))  {
			$sql .= ' ' . $filter . ' ';
		}
		if ($orderby != '') {
			$sql .= ' ' . $orderby;
		}
		else if ($orderby == '') {
			$sql .= ' ORDER BY nmap_scan_datetime desc';
		}
		return $sql;
	}
	
	public function get_displays() {
		// Not yet implemented.
	}

	/**
	 * Return the unique id
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return int
	 */
	public function get_id() {
	   return $this->id;
	}

	/**
	 * Return the timestampe
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return int
	 */
	public function get_dateteme() {
	   return $this->datetime;
	}
	
	public function save() {
		// No need to save, all data is static
		return false;
	}

} /* end of class Nmap_scan */

?>
