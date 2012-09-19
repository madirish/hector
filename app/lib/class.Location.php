<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Location.php
 *
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
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/**
 * Short description of class Location
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Location extends Maleable_Object implements Maleable_Object_Interface {
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Unique id
     *
     * @access private
     * @var int
     */
    protected $id = null;

	/**
	 * Location name
	 * 
	 * @var String
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
	 * @var Array
	 */
    public $host_ids = array();

    // --- OPERATIONS ---

    /**
     * Set up a new instance of this object.
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
		if ($id != '' && $id > 0) {
			$sql = array(
				'SELECT * FROM location WHERE location_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->location_id;
			$this->name = $result[0]->location_name;
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
	    		'DELETE FROM location WHERE location_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a location.
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
	 * @return SQL select string
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
	 */
	public function get_details() {
		$retval = '<table id="location_details">' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Location Name:</td><td>' . $this->get_name() . '</td></tr>' . "\n";
		$retval .= '</table>';
		return $retval;
	}

	public function get_displays() {
		return array('Name'=>'get_name');
	}
	
	/**
	 * Get an array of all the hosts associated with this
	 * LSP Group.  This is an expensive operation so it 
	 * isn't part of the constructor.
	 * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return array of host id's
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
	 * Return the name string
	 * 
	 * @access public
	 * @return String
	 */
    public function get_name() {
		return $this->name;
    }

	/**
	 * Save the object for persistence
	 * 
	 * @access public
	 * @return void
	 */
    public function save() {
    	if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE location SET location_name = \'?s\' WHERE location_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO location SET location_name = \'?s\'',
    			$this->get_name()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

    public function set_name($name) {
    	if ($name != '')
    		$this->name = htmlspecialchars($name);
    	elseif ($name == '')
    		$this->name = '';
    }

} /* end of class Location */

?>