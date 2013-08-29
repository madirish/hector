<?php
/**
 * HECTOR - class.Location.php
 *
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
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/**
 * Location is the physical address for hosts in order
 * to track them in an inventory.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Location extends Maleable_Object implements Maleable_Object_Interface {
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
     * Unique id
     *
     * @access private
     * @var Int Unique id from the data layer
     */
    protected $id = null;

	/**
	 * Location name
	 * 
	 * @access private
	 * @var String Name of the location
	 */
    private $name;

	/**
	 * Hosts associated with this Location.  This
	 * is just a convenience (for reporting).
	 * There is no interface for altering this
	 * attribute.  This attribute isn't populated
	 * until get_host_ids method is called 
	 * explicitly.
	 * 
	 * @access public
	 * @var Array Array of host_ids for Hosts with this Location
	 */
    public $host_ids = array();

    // --- OPERATIONS ---

    /**
     * Set up a new instance of this object.
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int Optional unique ID of the location
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '' && $id > 0) {
			$sql = array(
				'SELECT * FROM location WHERE location_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->set_id($result[0]->location_id);
			$this->set_name($result[0]->location_name);
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
	    		'DELETE FROM location WHERE location_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a location.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array Array for use with the default CRUD template.
	 */
	public function get_add_alter_form() {

		return array (
			array('label'=>'Location name',
					'type'=>'text',
					'name'=>'locationname',
					'value_function'=>'get_name',
					'process_callback'=>'set_name')
		);
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @access public
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT location_id FROM location WHERE location_id > 0';
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
			$sql .= ' ORDER BY location_name';
		}
		return $sql;
	}
	
	/**
	 * The method to return the HTML for the details on this specific host
	 * 
	 * @access public
	 * @return String HTML for display in the template.
	 * @todo Move this material to a template
	 */
	public function get_details() {
		$retval = '<table id="location_details">' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Location Name:</td><td>' . $this->get_name() . '</td></tr>' . "\n";
		$retval .= '</table>';
		return $retval;
	}
	
	/**
	 * Get the generic displays for templates
	 * 
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return Array An array of display fields and lookup methods
	 */
	public function get_displays() {
		return array('Name'=>'get_name');
	}
	
	/**
	 * Get an array of all the hosts associated with this
	 * Support Group.  This is an expensive operation so it 
	 * isn't part of the constructor.
	 * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array An array of host id's
	 */
	public function get_host_ids() {
		$sql = array(
			'SELECT host_id FROM host WHERE location_id = ?i',
			$this->id
		);
		$result = $this->db->fetch_object_array($sql);
	    	if (is_array($result) && count($result) > 0) {
	    		foreach($result as $row) {
	    			$this->host_ids[] = $row->host_id;
	    		}
	    	}
	    return $this->host_ids;
	}

	/**
	 * Return the name string
	 * 
	 * @access public
	 * @return String HTML display safe Location name
	 */
    public function get_name() {
		return htmlentities($this->name);
    }

    /**
     * Persist the object to the data layer. On the save of 
     * a new record the id parameter is populated.
     * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean True if the save worked properly, false otherwise.
     */
    public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE location SET location_name = \'?s\' WHERE location_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO location SET location_name = \'?s\'',
    			$this->get_name()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->last_id > 0) {
	    		$this->set_id($result[0]->last_id);
	    	}
    	}
    	return $retval;
    }

	/**
	 * Set the name of the Location
	 * 
	 * @access public
	 * @param String Name of the Location
	 */
    public function set_name($name) {
    	$this->name = $name;
    }

} /* end of class Location */

?>