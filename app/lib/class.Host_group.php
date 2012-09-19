<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Host_group.php
 * This file is part of HECTOR.
 *
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

$explaination = "Host groups are logical aggregations of machines, generally assigned " .
		"to a specific support provider.  Host groups can be used to target specific scans " .
		"at targets.";
/**
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
require_once('class.Nmap_scan_result.php');

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/* user defined constants */
// section 127-0-0-1--4d23d2c8:125a23d9458:-8000:0000000000000CDE-constants begin
// section 127-0-0-1--4d23d2c8:125a23d9458:-8000:0000000000000CDE-constants end

/**
 * Short description of class Host_group
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Host_group extends Maleable_Object implements Maleable_Object_Interface {
    // --- ASSOCIATIONS ---



    // --- ATTRIBUTES ---

    /**
     * Unique id for the group from the database
     *
     * @access private
     * @var int
     */
	protected $id = null;
	
    /**
     * Name for the group
     *
     * @access private
     * @var String
     */
    private $name = null;


    // --- OPERATIONS ---

    /**
     * Populate the host group from the database
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
				'SELECT * FROM host_group WHERE host_group_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->host_group_id;
			$this->name = $result[0]->host_group_name;
		}
    }
    
    /**
     * Add a host to this host group
     * 
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param Host id
     * @return boolean
     */
    public function add_host_to_group($host_id) {
    	$host_id = intval($host_id);
    	$retval = FALSE;
    	if ($host_id > 0) {
    		$sql = array(
    				'INSERT INTO host_x_host_group ' .
    				'set host_group_id = \'?i\', host_id = \'?i\'',
    				$this->id,
    				$host_id);
    		if ($this->db->iud_sql($sql)) $retval = TRUE;
    	}
    	return $retval;
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
	    		'DELETE FROM host_group WHERE host_group_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	// Remove old mappings
	    	$sql = array(
	    		'DELETE FROM host_x_host_group WHERE host_group_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
    
    /**
     * Drop a host from this host group
     * 
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param Host id
     * @return boolean
     */
    public function delete_host_from_group($host_id) {
    	$host_id = intval($host_id);
    	$retval = FALSE;
    	if ($host_id > 0) {
    		$sql = array(
    				'DELETE FROM host_x_host_group ' .
    				'WHERE host_group_id = \'?i\' AND host_id = \'?i\'',
    				$this->id,
    				$host_id);
    		if ($this->db->iud_sql($sql)) $retval = TRUE;
    	}
    	return $retval;
    }

	public function get_add_alter_form() {
		// get the host groups array
		$hostgroups = array();
		$collection = new Collection('Host_group');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$hostgroups[] = array($element->get_id()=>$element->get_name());
			}
		}
		// if it's a new addition
		if ($this->id == NULL) {
			return array (
				array('label'=>'Group name',
						'type'=>'text',
						'name'=>'hostname',
						'value_function'=>'get_name',
						'process_callback'=>'set_name'),
				array('label'=>'Apply to all hosts?',
						'type'=>'select',
						'name'=>'applytoall',
						'options' => array('0'=>'No', '1'=>'Yes'),
						'value_function'=>'get_applytoall',
						'process_callback'=>'set_applytoall'),
			);
		}
		// Existing record (edit form)
		else {
			return array (
				array('label'=>'Group name',
						'type'=>'text',
						'name'=>'hostname',
						'value_function'=>'get_name',
						'process_callback'=>'set_name')
			);
		}
	}
	
	/**
	 * Applying the host_group to all hosts should only
	 * be done on add/edit, default to this query should
	 * always be 'no'.
	 */
	public function get_applytoall() {
		return 0;
	}

    /* This function directly supports the Collection class.
	 *
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT hg.host_group_id FROM host_group hg WHERE hg.host_group_id > 0';
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
		else {
			$sql .= ' ORDER BY hg.host_group_name';
		}
		return $sql;
	}

	public function get_displays() {
		return array('Group name'=>'get_name');
	}

	public function get_host_ids() {
		$retval = array();
		if ($this->id != null) {
			$sql = array(
				'select host_id from host_x_host_group where host_group_id = ?i',
				$this->id);
			$result = $this->db->fetch_object_array($sql);
			if (is_array($result)) {
				foreach($result as $record) $retval[] = $record->host_id;
			}
		}
		return $retval;
	}
	
	public function get_id() {
		return $this->id;
	}

    public function get_name() {
    	return $this->name;
    }

    /**
     *
     * @todo prevent duplicate names
     */
    public function save() {
    	if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE host_group SET host_group_name = \'?s\' WHERE host_group_id = \'?i\'',
	    		$this->name,
	    		$this->id
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		// Insert a new value
	    	$sql = array(
	    		'INSERT INTO host_group SET host_group_name = \'?s\'',
	    		$this->name
	    	);
	    	$this->db->iud_sql($sql);
	    	// Now set the id
	    	$this->id = mysql_insert_id();
    	}
    }
    
    /**
     * Allows us to create new host groups and apply them to all the
     * hosts we currently track.
     */
    public function set_applytoall($applytoall) {
    	if ($applytoall) {
	    	if (! isset($this->id)) {
	    		//We need to save the object so we can do the update
	    		$this->save();
	    	}
	  		$sql = array(
	  			'INSERT INTO host_x_host_group (host_group_id, host_id) ' . 
	  			'SELECT ?i, host_id from host',
	  			$this->id
	  		);
	  		$this->db->iud_sql($sql);
    	}
    }

    public function set_name($name) {
    	$this->name = htmlspecialchars($name);
    }

} /* end of class Host_group */

?>