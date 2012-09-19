<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Alert.php
 *
 * This file is part of HECTOR.
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

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
 * Short description of class Alert
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @todo enable MAC address tracking
 */
class Alert {
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---
    
    /**
     * Short description of attribute ip
     *
     * @access private
     * @var String
     */
    private $string = null;
    
    private $id = null;
  
	private $timestamp = null;
    
    private $host_id = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  int id
     * @return void
     */
    public function __construct($id = '')
    {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM alert WHERE alert_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->alert_id;
			$this->string = $result[0]->alert_string;
			$this->timestamp = $result[0]->alert_timestamp;
			$this->host_id = $result[0]->host_id;
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
	    		'DELETE FROM alert WHERE alert_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
    
    /* This function directly supports the Collection class.
	 * 
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = ' ORDER BY alert_timestamp DESC') {
		$query_args = array();
		$sql = 'SELECT alert_id FROM alert WHERE alert_id > 0';
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
	
	public function get_displays() {
		return array('Timestamp'=>'get_timestamp', 'Alert'=>'get_string', 'Host'=>'get_host_linked');
	}

	public function get_host_linked() {
		$host = new Host($this->get_host_id());
		$retval = '<a href="?action=details&object=host&id=' . 
				$this->get_host_id() . '">' .
				$host->get_name() . '</a>';
		return $retval;
	}
	
	private function get_host_id() {
		return intval($this->host_id);
	}
    /**
     * Short description of method get_id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int
     */
    private function get_id() {
       return $this->id;
    }

    /**
     * Short description of method get_ip
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return ip
     */
    public function get_string() {
    	return htmlspecialchars($this->string);
    }
    
    public function get_timestamp() {
    	return $this->timestamp;
    }
    
    public function save() {
    	if (($this->host_id == NULL) || ($this->string == NULL)) return false;
    	$sql = '';
    	if ($this->id == NULL) {
    		$sql = array('INSERT INTO alert (alert_timestamp, alert_string, host_id) ' .
    			' values (NOW(), \'?s\', ?i)',
    			$this->string,
    			$this->host_id);
    	}
    	else {
    		$sql = array('UPDATE alert set alert_timestamp = \'?d\', alert_string = \'?s\', host_id = ?i ' .
    			' where alert_id = ?d',
    			$this->timestamp,
    			$this->string,
    			$this->host_id,
    			$this->id);
    	}
	    $this->db->iud_sql($sql);
    }
    
    public function set_host_id($id) {
    	$this->host_id = intval($id);
    }
    
    public function set_string($string) {
    	$this->string = $string;
    }
    

} /* end of class Alert */

?>