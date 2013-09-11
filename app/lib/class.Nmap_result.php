<?php
/**
 * class.nmap_result.php
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
 
/**
 * Enable error reporting
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');

/**
 * nmap_result represents a single line of an 
 * NMAP scan of a target Host, so there is only 
 * one port/protocol and the state that was observed
 * from a scan.
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
class Nmap_result {
    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;
    
    /**
     * Instance of the Log
     * 
     * @access private
     * @var Log An instance of the Log
     */
    private $log = null;
    
    /**
     * The internal id of the record
     *
     * @access private
     * @var Int The unique id from the data layer
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
     * @var Int The state of the port from the state table
     */
    private $state = null;
    
    /**
     * The state id from the database
     *
     * @access private
     * @var Int The unique ID of the corresponding State
     */
    private $state_id = null;
    
    /**
     * The port number of the scan
     *
     * @access private
     * @var Int The port number
     */
    private $port_number = null;
    
    /**
     * tcp or udp
     * 
     * @access private
     * @var String TCP or UDP depending
     */
    private $protocol = null;
    
    /**
     * The host record id
     * 
     * @access private
     * @var Int The unique id of the corresponding Host
     */
    private $host_id = null;
    
    /**
     * The scan_id which should be a Unix timestamp
     * 
     * @access private
     * @var Int The scan_id which should be a Unix timestamp
     */
    private $scan_id = null;
    
    /**
     * The name of the service running on the port
     * 
     * @access private
     * @var String The name of the port service
     */
    private $service_name = null;
    
    /**
     * The version of the service running on the port
     * 
     * @access private
     * @var String The service version string from the scan
     */
    private $service_version = null;
    
    /**
     * The timestamp when the port observation was made
     * 
     * @access private
     * @var Timestamp The timestamp when the port observation was made
     */
    private $timestamp = null;
  

    // --- OPERATIONS ---

    /**
     * Build up the result object from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param  Int The (optional) unique id of the Nmap_result
     * @return void
     */
    public function __construct($id = '') {
			$this->db = Db::get_instance();
			$this->log = Log::get_instance();
			if ($id != '') {
				$sql = array(
					'SELECT nsr.*, s.state_state ' .
					'FROM nmap_result nsr, state s ' .
					'WHERE s.state_id = nsr.state_id AND nsr.nmap_result_id = ?i',
					$id
				);
				$result = $this->db->fetch_object_array($sql);
				if (! is_object($result[0])) {
					$this->log->write_error("Incorrect nmap_result constructor with id " . $id);
					print "Incorrect nmap_result constructor with id [" . $id . "]\n";
				}
				else {
					$this->set_id($result[0]->nmap_result_id);
					$this->set_host_id($result[0]->host_id);
					$this->set_port_number($result[0]->nmap_result_port_number);
					$this->set_protocol($result[0]->nmap_result_protocol);
					$this->set_scan_id($result[0]->scan_id);
					$this->set_state_id($result[0]->state_id);
					$this->state = $result[0]->state_state;
					$this->set_timestamp($result[0]->nmap_result_timestamp);
					$this->set_service_name($result[0]->nmap_result_service_name);
					$this->set_service_version($result[0]->nmap_result_service_version);
				}
				
			}
    }
    
    /**
     * Delete the record from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM nmap_result WHERE nmap_result_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
    }
    
    /** 
     * This function directly supports the Collection class.
     * 
	 * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String SQL select string
	 * @param String The optional filter for the SQL WHERE clause
	 * @param String The optional additions to the SQL ORDER BY clause
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT nsr.nmap_result_id ' . 
				'FROM nmap_result nsr, state s ' .
				'WHERE nsr.state_id = s.state_id and nsr.nmap_result_id > 0';
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
     * Get the host_id to build or refer to a Host object
     *
     * @access public
     * @author Sam Oldak, <sam@oldaks.com>
     * @return Int The Host id associated with this scan
     */
    public function get_host_id() {
        return (int) $this->host_id;
    }
    
    /**
     * Return the unique id from the data layer
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Int The unique id from the data layer.
     */
    public function get_id() {
        return (int) $this->id;
    } 
    
    /**
     * Get the structured table row to display this result
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String HTML for the standard display template.
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
     * @return String The actual state of the port.
     */
    public function get_state() {
    	return $this->state;
    }
     
   /**
     * Return the state_id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The unique ID of the state from the database
     */
    public function get_state_id() {
    	return $this->state_id;
    }

    /**
     * Return the port number for the scan
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The port number
     */
    public function get_port_number() {
        return (int) $this->port_number;
    }
    
    /**
     * Return the protocol or tcp if it isn't set
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The protocol of the port, defaults to TCP
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
     * @return Int The scan id as a Unix timestamp
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
     * @return String The HTML safe service name for the port
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
     * @return String The HTML safe version of the service
     */
    public function get_service_version() {
       return htmlentities($this->service_version);
    }
    
    /**
     * Get the timestamp of the scan
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Datetime The timestamp of the scan.
     */
    public function get_timestamp() {
       return $this->timestamp;
    }
    
    /**
     * Look up the latest result from an existing scan result.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return void
     * @param Int The unique Host id
     * @param Int The port number of interest
     * @param String The protocol we're looking up
     */
    public function lookup_scan($host_id, $port_number, $protocol) {
    	$sql = array(
				'SELECT nsr.*, s.state_state ' .
				'FROM nmap_result nsr, state s ' .
				'WHERE s.state_id = nsr.state_id AND nsr.host_id = ?i ' .
				'AND nsr.nmap_result_port_number = ?i ' .
				'AND nsr.nmap_result_protocol = \'?s\' ' .
				'ORDER BY nsr.nmap_result_timestamp DESC LIMIT 1',
				$host_id,
				$port_number,
				$protocol
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0]) && is_object($result[0])) {
				$this->set_id($result[0]->nmap_result_id);
				$this->set_host_id($result[0]->host_id);
				$this->set_port_number($result[0]->nmap_result_port_number);
				$this->set_protocol($result[0]->nmap_result_protocol);
				$this->set_scan_id($result[0]->scan_id);
				$this->set_state_id($result[0]->state_id);
				$this->set_state = $result[0]->state_state;
				$this->set_timestamp($result[0]->nmap_result_timestamp);
				$this->set_service_name($result[0]->nmap_result_service_name);
				$this->set_service_version($result[0]->nmap_result_service_version);
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
     * @return Boolean False if something goes awry
	 */    
    public function save() {
    	$retval = FALSE;
		if($this->host_id != NULL && 
			$this->port_number != NULL && 
			$this->state_id != NULL && 
			$this->scan_id != NULL) {
			// Clean out any old records for this port
			$sql = array('DELETE from nmap_result WHERE host_id = ?i AND nmap_result_port_number = ?i',
					$this->host_id,
					$this->port_number);
			$this->db->iud_sql($sql);
		
			if ($this->id != NULL ) {
				$sql = array('UPDATE nmap_result SET state_id=?i, ' .
						'nmap_result_port_number=?i, ' .
						'nmap_result_protocol=\'?s\', ' .
						'host_id=?i, ' .
						'nmap_result_service_name=\'?s\', ' .
						'nmap_result_service_version=\'?s\', ' .
						'nmap_result_timestamp=NOW(), ' .
						'nmap_result_is_new=0, ' .
						'scan_id=?i ' .
						'WHERE nmap_result_id = ?i', 
						$this->state_id,
						$this->port_number,
						$this->protocol,
						$this->host_id,
						$this->service_name,
						$this->service_version,
						$this->scan_id,
						$this->id
						);
				$retval = $this->db->iud_sql($sql);
			}
			else {
				$sql = array('INSERT INTO nmap_result ' . 
							'(state_id, ' .
							'nmap_result_port_number, ' .
							'nmap_result_protocol, ' .
							'host_id, ' .
							'nmap_result_service_name, ' .
							'scan_id, ' .
							'nmap_result_service_version, ' .
							'nmap_result_timestamp) ' . 
							' VALUES (?i, ?i, \'?s\', ?i, \'?s\', ?i, \'?s\', NOW())',
							$this->state_id,
							$this->port_number,
							$this->protocol,
							$this->host_id,
							$this->service_name,
							$this->scan_id,
							$this->service_version);
				$retval = $this->db->iud_sql($sql);
				// Now set the id
		    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
		    	$result = $this->db->fetch_object_array($sql);
		    	if (isset($result[0]) && $result[0]->last_id > 0) {
		    		$this->set_id($result[0]->last_id);
		    	}
			}
		}
		else {
			if ($this->scan_id == NULL) $this->log->write_error("Can't save nmap_result as scan_id is NULL");
			if ($this->host_id == NULL) $this->log->write_error("Can't save nmap_result as host_id is NULL");
			if ($this->port_number == NULL) $this->log->write_error("Can't save nmap_result as port_number is NULL");
			if ($this->protocol == NULL) $this->log->write_error("Can't save nmap_result as protocol is NULL");
			if ($this->state_id == NULL) {
				$this->log->write_error("Can't save nmap_result as state_id is NULL");
			}
		}
		return $retval;
    }
    
    /**
     * Set the port number to an integer
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Int The port number
     * @return Boolean False if something goes awry
     */
    public function set_port_number($number) {
    	$retval = FALSE;
    	$number = intval($number);
    	if ($number > -1 && $number < 65536) {
    		$this->port_number = intval($number);
    		$retval = TRUE;
    	}
    	return $retval;
    }
    
    /**
     * Set the protocol to tcp or udp
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The protocol ('tcp' or 'udp')
     * @return Boolean False if something goes awry
     */
    public function set_protocol($proto) {
    	$retval = FALSE;
    	if ($proto == 'tcp' || $proto == 'udp') {
    		$this->protocol = $proto;
    		$retval = TRUE;
    	}
    	return $retval;
    }
    
    /**
     * Set the scan id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Int the unique id of the scan
     */
    public function set_scan_id($id) {
    	$this->scan_id = intval($id);
    }
    
    /**
     * Set the state id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Int The unique id fo the state
     * @return False if something goes awry (for instance, state_id > 5 or < 0)
     */
    public function set_state_id($state) {
    	$retval = TRUE;
    	$state = intval($state);
    	// restrict to good values
    	if ($state < 0 || $state > 5) $retval =  false;
    	else {
    		$this->state_id = $state;
    	}
    	return $retval;
    }

	/**
	 * Set the host id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Int The unique host id
	 * @todo Check to make sure the host exists?
	 */    
    public function set_host_id($id) {
    	$this->host_id = intval($id);
    }
	
    /**
     * Set the object's unique id
     *
     * @access protected
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  int The unique id for hte object
     * @return void
     */
    protected function set_id($id) {
       $this->id = (int) $id;
    }
    
    /**
     * Set the service name
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The name of the service
     */
    public function set_service_name($name) {
    	$this->service_name = $name;
    }
    
    /**
     * Set the version of the service
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The service version string
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

} /* end of class Nmap_result */

?>