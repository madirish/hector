<?php

error_reporting(E_ALL);

/**
 * class.Nmap_scan_result.php
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');

/**
 * Nmap_scan_result represents a single line of an 
 * NMAP scan of a target Host, so there is only 
 * one port/protocol and the state that was observed
 * from a scan.
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @todo capture banner strings
 */
class Nmap_scan_result
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---
    
    /**
     * The internal id of the record
     *
     * @access private
     * @var int
     */
    private $id = 0;
    
    /**
     * The state of the port
     * 1 = open
     * 2 = closed
     * 3 = filtered
     * 4 = open|filtered
     * 5 = other
     *
     * @access private
     * @var int
     */
    private $state = null;
    /**
     * The state id from the database
     *
     * @access private
     * @var int
     */
    private $state_id = null;
    
    /**
     * The port number of the scan
     *
     * @access private
     * @var int
     */
    private $port_number = null;
    
    /**
     * tcp or udp
     * 
     * @access private
     * @var string
     */
    private $protocol = null;
    
    /**
     * The host record id
     * 
     * @access private
     * @var int
     */
    private $host_id = null;
    /**
     * The scan_id which should be a Unix timestamp
     * 
     * @access private
     * @var int
     */
    private $scan_id = null;
    
    /**
     * The name of the service running on the port
     * 
     * @access private
     * @var String
     */
    private $service_name = null;
    
    /**
     * The version of the service running on the port
     * 
     * @access private
     * @var String
     */
    private $service_version = null;
    
    /**
     * The timestamp when the port observation was made
     * 
     * @access private
     * @var timestamp
     */
    private $timestamp = null;
  

    // --- OPERATIONS ---

    /**
     * Build up the result object from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param  int id
     * @return void
     */
    public function __construct($id = '') {
			$this->db = Db::get_instance();
			$this->log = Log::get_instance();
			if ($id != '') {
				$sql = array(
					'SELECT nsr.*, s.state_state ' .
					'FROM nmap_scan_result nsr, state s ' .
					'WHERE s.state_id = nsr.state_id AND nsr.nmap_scan_result_id = ?i',
					$id
				);
				$result = $this->db->fetch_object_array($sql);
				if (! is_object($result[0])) {
					$this->log->write_error("Incorrect nmap_scan_result constructor with id " . $id);
					print "Incorrect nmap_scan_result constructor with id [" . $id . "]\n";
				}
				else {
					$this->id = $result[0]->nmap_scan_result_id;
					$this->host_id = $result[0]->host_id;
					$this->port_number = $result[0]->nmap_scan_result_port_number;
					$this->protocol = $result[0]->nmap_scan_result_protocol;
					$this->scan_id = $result[0]->scan_id;
					$this->state_id = $result[0]->state_id;
					$this->state = $result[0]->state_state;
					$this->timestamp = $result[0]->nmap_scan_result_timestamp;
					$this->service_name = $result[0]->nmap_scan_result_service_name;
					$this->service_version = $result[0]->nmap_scan_result_service_version;
				}
				
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
	    		'DELETE FROM nmap_scan_result WHERE nmap_scan_result_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
    
    /** 
     * This function directly supports the Collection class.
     * 
	 * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT nsr.nmap_scan_result_id ' . 
				'FROM nmap_scan_result nsr, state s ' .
				'WHERE nsr.state_id = s.state_id and nsr.nmap_scan_result_id > 0';
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
		return $sql;
	}

    /**
     * Get the unique id for this result.
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int
     */
    public function get_id() {
        return (int) $this->id;
    }
    
     /**
     * Get the host_id to build or refer to a Host object
     *
     * @access public
     * @author Sam Oldak, <sam@oldaks.com>
     * @return int
     */
    public function get_host_id() {
        return (int) $this->host_id;
    }
    
    /**
     * Get the structured table row to display this result
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_details() {
    	$class = ($this->get_state() == "open") ? "open" : "closed";
    	$retval = '';
    	$retval .= '<tr>';
    	$retval .= '<td class="port_number">' . $this->get_port_number() . '/' . $this->get_protocol() . '</td>';
    	$retval .= '<td class="port_state">' . $this->get_state() . '</td>';
    	$retval .= '<td class="scan_timestamp">' . $this->get_timestamp() . '</td>';
    	$retval .= '<td class="port_service_name">' . $this->get_service_name() . '</td>';
    	$retval .= '<td class="port_service_version">' . $this->get_service_version() . '</td>';
    	$retval .= '</tr>' . "\n";
    	return $retval;
    }
    
   /**
     * Return the state (open, closed, filtered)
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_state() {
    	return $this->state;
    }
     
   /**
     * Return the state_id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int
     */
    public function get_state_id() {
    	return $this->state_id;
    }

    /**
     * Return the port number for the scan
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int
     */
    public function get_port_number()
    {
        return (int) $this->port_number;
    }
    
    /**
     * Return the protocol or tcp if it isn't set
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_protocol() {
    	if ($this->protocol == null) $this->protocol = 'tcp'; // default
    	return $this->protocol;
    }
    
    /**
     * Get the scan id of the NMAP scan run
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_scan_id() {
    	return $this->scan_id;
    }

    /**
     * Get the service name as detected by NMAP
     * Ex: http
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_service_name() {
       return htmlentities($this->service_name);
    }
    
    /**
     * Get the version of the service if it has been detected
     * ex: SSHd version 2.5.4
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_service_version() {
       return htmlentities($this->service_version);
    }
    
    /**
     * Get the timestamp of the scan
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Datetime
     */
    public function get_timestamp() {
       return $this->timestamp;
    }
    
    /**
     * Look up an existing scan result, or if one 
     * cannot be found create a new one.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return null
     * @param int $host_id
     * @param int $port_number
     * @param String $protocol
     */
    public function lookup_scan($host_id, $port_number, $protocol) {
    	$sql = array(
				'SELECT nsr.*, s.state_state ' .
				'FROM nmap_scan_result nsr, state s ' .
				'WHERE s.state_id = nsr.state_id AND nsr.host_id = ?i ' .
				'AND nsr.nmap_scan_result_port_number = ?i ' .
				'AND nsr.nmap_scan_result_protocol = \'?s\'',
				$host_id,
				$port_number,
				$protocol
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0]) && is_object($result[0])) {
				$this->id = $result[0]->nmap_scan_result_id;
				$this->host_id = $result[0]->host_id;
				$this->port_number = $result[0]->nmap_scan_result_port_number;
				$this->protocol = $result[0]->nmap_scan_result_protocol;
				$this->scan_id = $result[0]->scan_id;
				$this->state_id = $result[0]->state_id;
				$this->state = $result[0]->state_state;
				$this->timestamp = $result[0]->nmap_scan_result_timestamp;
				$this->service_name = $result[0]->nmap_scan_result_service_name;
				$this->service_version = $result[0]->nmap_scan_result_service_version;
			}
    }

	/**
	 * Save this record.
	 * 
	 * Also, check and see if this record is an update of 
	 * a previous scan result (i.e. does it represent a 
	 * new state of a previously observed port).  If this is 
	 * the case generate an alert.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return boolean
	 */    
    public function save() {
			if($this->host_id != NULL && $this->port_number != NULL && $this->state_id != NULL && $this->scan_id != NULL) {
				// Clean out any old records for this port
				$sql = array('DELETE from nmap_scan_result where host_id = ?i and nmap_scan_result_port_number = ?i',
    					$this->host_id,
    					$this->port_number);
    		$this->db->iud_sql($sql);
    		
				if ($this->id != NULL ) {
					$sql = array('UPDATE nmap_scan_result set state_id=?i, ' .
							' nmap_scan_result_port_number=?i, nmap_scan_result_protocol=\'?s\', host_id=?i, nmap_scan_result_service_name=\'?s\', ' .
							' nmap_scan_result_service_version=\'?s\', nmap_scan_result_timestamp=NOW(), ' .
							' nmap_scan_result_is_new=0, scan_id=?i ' .
							' where nmap_scan_result_id = ?i', 
							$this->state_id,
							$this->port_number,
							$this->protocol,
							$this->host_id,
							$this->service_name,
							$this->service_version,
							$this->scan_id,
							$this->id
							);
				}
				else {
					$sql = array('INSERT INTO nmap_scan_result ' . 
								'(state_id, nmap_scan_result_port_number, nmap_scan_result_protocol, host_id, nmap_scan_result_service_name, ' .
								'scan_id, nmap_scan_result_service_version, nmap_scan_result_timestamp) ' . 
								' VALUES (?i, ?i, \'?s\', ?i, \'?s\', ?i, \'?s\', NOW())',
								$this->state_id,
								$this->port_number,
								$this->protocol,
								$this->host_id,
								$this->service_name,
								$this->scan_id,
								$this->service_version);
				}
				$this->db->iud_sql($sql);
			}
			else {
				if ($this->scan_id == NULL) $this->log->write_error("Can't save nmap_scan_result as scan_id is NULL");
				if ($this->host_id == NULL) $this->log->write_error("Can't save nmap_scan_result as host_id is NULL");
				if ($this->port_number == NULL) $this->log->write_error("Can't save nmap_scan_result as port_number is NULL");
				if ($this->protocol == NULL) $this->log->write_error("Can't save nmap_scan_result as protocol is NULL");
				if ($this->state_id == NULL) {
					$this->log->write_error("Can't save nmap_scan_result as state_id is NULL");
				}
				return false;
			}
    }
    
    /**
     * Set the port number to an integer
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param int $number
     */
    public function set_port_number($number) {
    	$this->port_number = intval($number);
    }
    
    /**
     * Set the protocol to tcp or udp
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String $proto
     */
    public function set_protocol($proto) {
    	if ($proto == 'tcp' || $proto == 'udp') {
    		$this->protocol = $proto;
    	}
    }
    
    /**
     * Set the scan id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param int $id
     */
    public function set_scan_id($id) {
    	$this->scan_id = intval($id);
    }
    
    /**
     * Set the state id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param int $state
     */
    public function set_state_id($state) {
    	$state = intval($state);
    	// restrict to good values
    	if ($state < 0 || $state > 5) return false;
    	else {
    		$this->state_id = $state;
    	}
    	return true;
    }

	/**
	 * Set the host id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param int $id
	 * @todo Check to make sure the host exists?
	 */    
    public function set_host_id($id) {
    	$this->host_id = intval($id);
    }
    
    /**
     * Set the service name
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String $name
     */
    public function set_service_name($name) {
    	$this->service_name = $name;
    }
    
    /**
     * Set the version of the service
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String $ver
     */
    public function set_service_version($ver) {
    	$this->service_version = $ver;
    }
    
    /**
     * Set the timestamp of the scan to now
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     */
    public function set_timestamp() {
    	$this->timestamp = date( 'Y-m-d H:i:s' );
    }

} /* end of class Nmap_scan_result */

?>