<?php
/**
 * class.nessus_result.php
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author James Davis <jamed@sas.upenn.edu>
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
 * nessus_result represents a single vulnerability of a 
 * Nessus scan of a target Host
 *
 * @access public
 * @author James Davis <jamed@sas.upenn.edu>
 * @package HECTOR
 */
class Nessus_result {
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
     * @var Int The unique id
     */
    private $id = 0;

    /**
     * The host record id
     * 
     * @access private
     * @var Int The unique id of the corresponding Host
     */
    private $host_id = null;

    /**
     * The id of the scan
     * 
     * @access private
     * @var Int The unique id of the corresponding scan
     */
    private $scan_id = null;

    /**
     * The port number of the scan
     *
     * @access private
     * @var Int The port number
     */
    private $port_number = null;

    /**
     * The name of the service running on the port
     * 
     * @access private
     * @var String The name of the port service
     */
    private $service_name = null;
    
    /**
     * tcp or udp
     * 
     * @access private
     * @var String TCP or UDP depending
     */
    private $protocol = null;
    
    /**
     * The id of the scan
     * 
     * @access private
     * @var Int The plugin id
     */
    private $plugin_id = null;
    
    /**
     * The risk level of the plugin
     * 0 = informational
     * 1 = low risk
     * 2 = medium risk
     * 3 = high risk
     * 4 = critical risk
     *
     * @access private
     * @var Int The severity of the vulnerability
     */
    private $severity = null;
    
    /**
     * The name of the plugin or vulnerability
     * 
     * @access private
     * @var String The name of the plugin name
     */
    private $plugin_name = null;
    
    /**
     * The description of the plugin
     * 
     * @access private
     * @var String description of plugin
     */
    private $description = null;
    
    /**
     * The output from the plugin
     * 
     * @access private
     * @var String The output of the plugin
     */
    private $plugin_output = null;
    
    /**
     * The solution to the vulnerability
     * 
     * @access private
     * @var String The solution of the plugin
     */
    private $solution = null;
    
    /**
     * The timestamp when the scan was run
     * 
     * @access private
     * @var Timestamp The timestamp when the scan was run
     */
    private $timestamp = null;
    
    // --- OPERATIONS ---

    /**
     * Build up the result object from the database
     *
     * @access public
     * @author James Davis, <jamed@sas.upenn.edu>
     * @param  Int The unique id of the Nessus_result
     * @return void
     */
    public function __construct($id = '') {
			$this->db = Db::get_instance();
			$this->log = Log::get_instance();
			if ($id != '') {
				$sql = array(
					
				);
				$result = $this->db->fetch_object_array($sql);
				if (! is_object($result[0])) {
					$this->log->write_error("Incorrect nessus_result constructor with id " . $id);
					print "Incorrect nessus_result constructor with id [" . $id . "]\n";
				}
				else {
					$this->set_id($result[0]->nessus_result_id);
					$this->set_host_id($result[0]->host_id);
					$this->set_scan_id($result[0]->scan_id);
					$this->set_port_number($result[0]->nessus_result_port_number);
					$this->set_service_name($result[0]->nessus_result_service_name);
					$this->set_protocol($result[0]->nessus_result_protocol);
					$this->set_plugin_id($result[0]->nessus_result_plugin_id);
					$this->set_severity_id($result[0]->severity_id);
					$this->set_severity = $result[0]->severity_severity;
					$this->set_plugin_name($result[0]->nessus_result_plugin_name);
					$this->set_description($result[0]->nessus_result_description);
					$this->set_plugin_output($result[0]->nessus_result_plugin_output);
					$this->set_solution($result[0]->nessus_result_solution);
					$this->set_timestamp($result[0]->nessus_result_timestamp);
				}
				
			}
    }
    
    /**
     * Delete the record from the database
     *
     * @access public
     * @author James Davis, <jamed@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM nessus_result WHERE nessus_result_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
    }
    
    /**
     * Get the host_id to build or refer to a Host object
     *
     * @access public
     * @author James Davis, <jamed@sas.upenn.edu.com>
     * @return Int The Host id associated with this scan
     */
    public function get_host_id() {
        return (int) $this->host_id;
    }
    
    /**
     * Return the unique id from the data layer
     *
     * @access public
     * @author James Davis, <jamed@sas.upenn.edu>
     * @return Int The unique id from the data layer.
     */
    public function get_id() {
        return (int) $this->id;
    } 
    
    /**
     * Return the unique scan id
     * 
     * @access private
     * @var Int The id associated with the scan
     */
     public function get_scan_id() {
     	return (int) $this->scan_id;	
     }
    
    
    /**
     * Return the severity name(informational, low risk...)
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return String The severity of the vulnerability
     */
    public function get_severity() {
    	return $this->severity;
    }
     
    /**
     * Return the severity_id
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return Int The unique ID of the severity from the database
     */
    public function get_severity_id() {
    	return (int) $this->severity_id;
    }

    /**
     * Return the port number for the scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return Int The port number
     */
    public function get_port_number() {
        return (int) $this->port_number;
    }
    
    /**
     * Return the protocol
     * 
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return String The protocol of the port
     */
    public function get_protocol() {
    	return $this->protocol;
    }
    
    /**
     * Get the plugin id of the Nessus scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return Int The plugin id
     */
    public function get_plugin_id() {
    	return (int) $this->plugin_id;
    }

    /**
     * Get the plugin name of the Nessus scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return String The plugin name of the scan
     */
    public function get_plugin_name() {
       return $this->plugin_name;
    }
    
    /**
     * Get the description of the Nessus scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return String The description of the scan
     */
    public function get_description() {
       return $this->description;
    }
    
    /**
     * Get plugin output of the Nessus scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return String The plugin output of the scan
     */
    public function get_plugin_output() {
       return $this->plugin_output;
    }
    
    /**
     * Get the solution of the Nessus scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return String The solution of the scan
     */
    public function get_solution() {
       return $this->solution;
    }
    
    /**
     * Get the timestamp of the scan
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return Datetime The timestamp of the scan.
     */
    public function get_timestamp() {
       return $this->timestamp;
    }
    
    /**
     * Look up the latest result from an existing scan result.
     * 
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @return void
     * @param Int The unique Host id
     * @param String The plugin name
     * @param Int The severity id
     */
    public function lookup_scan($host_id, $plugin_name, $severity_id) {
    	$sql = array(
				'SELECT nr.*, s.severity_severity ' .
				'FROM nessus_result nr, severity s ' .
				'WHERE s.severity_id = nr.severity_id AND nr.host_id = ?i ' .
				'AND nr.nessus_result_plugin_name = ?i ' .
				'ORDER BY nr.nessus_result_timestamp DESC LIMIT 1',
				$host_id,
				$plugin_name,
				$severity_id
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0]) && is_object($result[0])) {
				$this->set_id($result[0]->nessus_result_id);
				$this->set_host_id($result[0]->host_id);
				$this->set_scan_id($result[0]->scan_id);
				$this->set_port_number($result[0]->nessus_result_port_number);
				$this->set_service_name($result[0]->nessus_result_service_name);
				$this->set_protocol($result[0]->nessus_result_protocol);
				$this->set_plugin_id($result[0]->nessus_result_plugin_id);
				$this->set_severity_id($result[0]->severity_id);
				$this->set_severity = $result[0]->severity_severity;
				$this->set_plugin_name($result[0]->nessus_result_plugin_name);
				$this->set_description($result[0]->nessus_result_description);
				$this->set_plugin_output($result[0]->nessus_result_plugin_output);
				$this->set_solution($result[0]->nessus_result_solution);
				$this->set_timestamp($result[0]->nessus_result_timestamp);
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
     * @author James Davis <jamed@sas.upenn.edu>
     * @return Boolean False if something goes awry
	 */    
    public function save() {
    	$retval = FALSE;
		if($this->host_id != NULL &&
			$this->plugin_id != NULL && 
			$this->plugin_name != NULL && 
			$this->severity_id != NULL && 
			$this->scan_id != NULL) {
			// Clean out any old records for this port
			$sql = array('DELETE from nessus_result WHERE host_id = ?i AND scan_id = ?i',
					$this->host_id,
					$this->scan_id);
			$this->db->iud_sql($sql);
		
			if ($this->id != NULL ) {
				$sql = array('UPDATE nessus_result SET host_id=?i, ' .
						'severity_id=?i, ' .
						'scan_id=?i, ' .
						'nessus_result_port_number=?i, ' .
						'nessus_result_service_name=\'?s\', ' .
						'nessus_result_protocol=\'?s\', ' .
						'nessus_result_plugin_id=?i, ' .
						'nessus_result_plugin_name=\'?s\', ' .
						'nessus_result_description=\'?s\', ' .
						'nessus_result_plugin_output=\'?s\', ' .
						'nessus_result_solution=\'?s\', ' .
						'nessus_result_timestamp=NOW(), ' .
						'WHERE nessus_result_id = ?i', 
						$this->host_id,
						$this->severity_id,
						$this->scan_id,
						$this->port_number,
						$this->service_name,
						$this->protocol,
						$this->plugin_id,
						$this->plugin_name,
						$this->description,
						$this->plugin_output,
						$this->solution,
						$this->id
						);
				$retval = $this->db->iud_sql($sql);
			}
			else {
				$sql = array('INSERT INTO nessus_result ' .
							'(host_id, ' . 
							'severity_id, ' .
							'scan_id, ' .
							'nessus_result_port_number, ' .
							'nessus_result_service_name, ' .
							'nessus_result_protocol, ' .
							'nessus_result_plugin_id, ' .
							'nessus_result_plugin_name, ' .
							'nessus_result_description, ' .
							'nessus_result_plugin_output, ' .
							'nessus_result_solution, ' .
							'nessus_result_timestamp) ' . 
							' VALUES (?i, ?i, ?i, ?i, \'?s\', \'?s\', ?i, \'?s\', \'?s\', \'?s\', \'?s\', NOW())',
							$this->host_id,
							$this->severity_id,
							$this->scan_id,
							$this->port_number,
							$this->service_name,
							$this->protocol,
							$this->plugin_id,
							$this->plugin_name,
							$this->description,
							$this->plugin_output,
							$this->solution);
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
			if ($this->scan_id == NULL) $this->log->write_error("Can't save nessus_result as scan_id is NULL");
			if ($this->host_id == NULL) $this->log->write_error("Can't save nessus_result as host_id is NULL");
			if ($this->severity_id == NULL) $this->log->write_error("Can't save nessus_result as state_id is NULL");
			if ($this->plugin_id == NULL) $this->log->write_error("Can't save nessus_result as plugin_id is NULL");
		}
		return $retval;
    }
    
    /**
     * Set the scan id
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param Int the unique id of the scan
     */
    public function set_scan_id($id) {
    	$this->scan_id = intval($id);
    }
    
    /**
     * Set the severity id
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param Int The unique id of the severity
     * @return False if something goes awry
     */
    public function set_severity_id($severity) {
    	$retval = TRUE;
    	$severity = intval($severity);
    	// restrict to good values
    	if ($severity < 0 || $severity > 4) $retval =  false;
    	else {
    		$this->severity_id = $severity;
    	}
    	return $retval;
    }

	/**
	 * Set the host id
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
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
     * @author James Davis <jamed@sas.upenn.edu>
     * @param  int The unique id for hte object
     * @return void
     */
    protected function set_id($id) {
       $this->id = (int) $id;
    }
    
    /**
     * Set the port number to an integer
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
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
     * Set the service name
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param String The name of the service
     */
    public function set_service_name($name) {
    	$this->service_name = $name;
    }
    
    /**
     * Set the protocol
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param String The protocol given
     */
    public function set_protocol($protocol) {
    	$this->protocol = $protocol;
    }
    
    /**
     * Set the plugin id
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param Int The plugin id of the vulnerability
     */
    public function set_plugin_id($id) {
    	$this->plugin_id = $id;
    }
    
    /**
     * Set the plugin name
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param String The plugin name of the vulnerability
     */
    public function set_plugin_name($name) {
    	$this->plugin_name = $name;
    }
    
    /**
     * Set the description of the vulnerability
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param String The description of the vulnerability
     */
    public function set_description($desc) {
    	$this->description = $desc;
    }
    
    /**
     * Set the plugin output of the vulnerability
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param String The plugin output of the vulnerability
     */
    public function set_plugin_output($output) {
    	$this->plugin_output = $output;
    }
    
    /**
     * Set the solution of the vulnerability
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     * @param String The solution of the vulnerability
     */
    public function set_solution($solution) {
    	$this->solution = $solution;
    }
    
    /**
     * Set the timestamp of the scan to now
     *
     * @access public
     * @author James Davis <jamed@sas.upenn.edu>
     */
    public function set_timestamp() {
    	$this->timestamp = date( 'Y-m-d H:i:s' );
    }
    
} /* end of class Nessus_result */
?>