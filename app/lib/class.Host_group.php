<?php
/**
 * HECTOR - class.Host_group.php
 * This file is part of HECTOR.
 *
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.28
 */
 
/**
 * Enable error reporting
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/*$explaination = "Host groups are logical aggregations of machines, generally assigned " .
		"to a specific support provider.  Host groups can be used to target specific scans " .
		"at targets.";*/
		
/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');


/**
 * Host_groups are containers for hosts in order to distinguish them.
 * For instance, Host_groups could be created for LAN machines,
 * DMZ servers, critical hosts, or web servers.  This is merely an
 * organizational tool, used for group access and targeting scans.
 *
 * @access public
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Host_group extends Maleable_Object implements Maleable_Object_Interface {
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
     * Unique id for the group from the database
     *
     * @access protected
     * @var Int	The unique ID
     */
	protected $id = null;
	
    /**
     * Name for the group
     *
     * @access private
     * @var String The name of the Host_group
     */
    private $name = null;
    
    /**
     * A description of the group
     *
     * @access private
     * @var String A more verbose description of the Host_group
     */
    private $detail = null;


    // --- OPERATIONS ---

    /**
     * Populate the host group from the database
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The Host_group id, or none for a blank object
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM host_group WHERE host_group_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
            if (isset($result[0]) && is_object($result[0])) {
    			$this->id = $result[0]->host_group_id;
    			$this->name = $result[0]->host_group_name;
                $this->detail = $result[0]->host_group_detail;
            }
		}
    }
    
    /**
     * Add a host to this host group
     * 
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param Int The Host unique id
     * @return Boolen False if something goes wrong
     */
    public function add_host_to_group($host_id) {
    	$host_id = intval($host_id);
    	$retval = FALSE;
    	if ($host_id != 0 ) {
    		$sql = array(
    				'INSERT INTO host_x_host_group ' .
    				'set host_group_id = \'?i\', host_id = \'?i\'',
    				$this->id,
    				$host_id);
            if (! in_array($host_id, $this->get_host_ids())) {
            	$retval = $this->db->iud_sql($sql);
            }
            else {
            	$retval = TRUE;
            }
    	}
    	return $retval;
    }

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean FALSE if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM host_group WHERE host_group_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Remove old mappings
	    	$sql = array(
	    		'DELETE FROM host_x_host_group WHERE host_group_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
    }
    
    /**
     * Drop a host from this host group
     * 
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param Int The Host id
     * @return Boolean False if soemthing goes wrong.
     */
    public function delete_host_from_group($host_id) {
    	$host_id = intval($host_id);
    	$retval = FALSE;
    	if ($host_id != 0) {
    		$sql = array(
    				'DELETE FROM host_x_host_group ' .
    				'WHERE host_group_id = \'?i\' AND host_id = \'?i\'',
    				$this->id,
    				$host_id);
    		if ($this->db->iud_sql($sql)) $retval = TRUE;
    	}
    	return $retval;
    }

	/**
	 * Generate the HTML for the form used to add or 
	 * edit a host group.
	 * 
     * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Array Array used to populate the default CRUD form
	 */
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
                array('label'=>'Description',
                        'type'=>'text',
                        'name'=>'detail',
                        'value_function'=>'get_detail',
                        'process_callback'=>'set_detail'),
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
						'name'=>'groupname',
						'value_function'=>'get_name',
						'process_callback'=>'set_name'),
                array('label'=>'Description',
                        'type'=>'text',
                        'name'=>'detail',
                        'value_function'=>'get_detail',
                        'process_callback'=>'set_detail')
			);
		}
	}
	
	/**
	 * This method supports the get_add_alter_form method.
	 * 
	 * Applying the host_group to all hosts should only
	 * be done on add/edit, default to this query should
	 * always be 'no'.
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access private
	 * @return Int Zero
	 */
	public function get_applytoall() {
		return 0;
	}

    /**
     * This function directly supports the Collection class.
	 *
     * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The SQL select string
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

    /**
     * Return the description of this host group.
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return String The description of this host group.
     */
    public function get_detail() {
        return $this->detail;
    }

	/**
	 * Get the display criteria for the add/edit/alter form.
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 * @return Array Display details for the default template
	 */
	public function get_displays() {
		return array('Group name'=>'get_name',
                     'Description' => 'get_detail');
	}

	/**
	 * Get all the hosts that are assigned to this group.
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 * @return Array List of host_id integers
	 */
	public function get_host_ids() {
		$retval = array();
		if ($this->id != null) {
			$sql = array(
				'select host_id from host_x_host_group where host_group_id = ?i',
				$this->id);
			$result = $this->db->fetch_object_array($sql);
			if (is_array($result)) {
				foreach($result as $record) $retval[] = intval($record->host_id);
			}
		}
		return $retval;
	}

	/**
	 * Get all the hosts that respond on ports or we have details on
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 * @return Array List of host_id integers
	 */
	public function get_live_host_ids() {
		$retval = array();
		if ($this->id != null) {
			$sql = array(
				'select distinct(x.host_id) 
					from host_x_host_group x, host h, nmap_result n 
					where x.host_id = h.host_id 
						AND n.host_id = x.host_id 
						AND x.host_group_id = ?i',
				$this->id);
			$result = $this->db->fetch_object_array($sql);
			if (is_array($result)) {
				foreach($result as $record) $retval[] = intval($record->host_id);
			}
		}
		return $retval;
	}
	
	/**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Host group';
    } 

	/**
	 * Return the name of this host group.
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 * @return String The name of this host group.
	 */
    public function get_name() {
    	return $this->name;
    }

    /**
     * Save the object, assuming it is new, otherwise simply update it.
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Boolean False if something goes wrong.
     */
    public function save() {
    	$retval = FALSE;
    	if ($this->get_id() > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE host_group SET host_group_name = \'?s\', host_group_detail = \'?s\' WHERE host_group_id = \'?i\'',
	    		$this->get_name(),
                $this->get_detail(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		// Check if the name exists
    		$sql = array(
				'select host_group_id from host_group where host_group_name = \'?s\'',
				$this->get_name(),
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0]->host_group_id)) {
				$this->id = $result[0]->host_group_id;
				$this->log->write_message('Attempt to add duplicate host_group: ' . $this->get_name());
				// Return true but don't actually add the new group
				$retval = TRUE;
			}
			else {
	    		// Insert a new value
		    	$sql = array(
		    		'INSERT INTO host_group SET host_group_name = \'?s\', host_group_detail = \'?s\'',
		    		$this->get_name(),
                    $this->get_detail()
		    	);
		    	$retval = $this->db->iud_sql($sql);
		    	// Now set the id
		    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
		    	$result = $this->db->fetch_object_array($sql);
		    	if (isset($result[0]) && $result[0]->last_id > 0) {
		    		$this->set_id($result[0]->last_id);
		    	}
		    	$this->log->write_message('Added new host group: ' . $this->get_name());	
			}
    	}
		return $retval;
    }
    
    /**
     * Allows us to create new host groups and apply them to all the
     * hosts we currently track.
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @param Int Non-zero indicates apply to all
     * @return Boolean False if something goes awry
     */
    public function set_applytoall($applytoall) {
    	$retval = FALSE;
    	if (intval($applytoall) > 0) {
	    	if (! isset($this->id)) {
	    		//We need to save the object so we can do the update
	    		$this->save();
	    	}
	  		$sql = array(
	  			'INSERT INTO host_x_host_group (host_group_id, host_id) ' . 
	  			'SELECT ?i, host_id from host',
	  			$this->get_id()
	  		);
	  		$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
    }

    /**
     * Set the sanitized description of this host group.
     * 
     * @param String The description of the host_group
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     */
    public function set_detail($detail) {
        $this->detail = htmlspecialchars($detail);
    }

	/**
	 * Set the sanitized name of this host group.
	 * 
	 * @param String The name of the host_group
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 */
    public function set_name($name) {
    	$this->name = htmlspecialchars($name);
    }

} /* end of class Host_group */

?>