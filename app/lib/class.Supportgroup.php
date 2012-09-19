<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Supportgroup.php
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
 * Support group is an object that refers to the organizational
 * unit responsible for supporting a particular host or hosts.
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Supportgroup extends Maleable_Object implements Maleable_Object_Interface {
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Unique id
     *
     * @access protected
     * @var int
     */
    protected $id = null;

	/**
	 * Name of the Support group
	 * 
   * @access private
	 * @var String
	 */
    private $name;
    
    /**
     * The contact e-mail for the group
     * 
     * @access private
     * @var String
     */
    private $email;

	/**
	 * Hosts associated with this Support group.  This
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
     * Set up a new instance of this object
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
					'SELECT * FROM supportgroup WHERE supportgroup_id = ?i',
						$id
				);
				$result = $this->db->fetch_object_array($sql);
				if (is_array($result) && isset($result[0])) {
					$this->id = $result[0]->supportgroup_id;
					$this->name = $result[0]->supportgroup_name;
					$this->email = $result[0]->supportgroup_email;
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
	    		'DELETE FROM supportgroup WHERE supportgroup_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a tag.
	 */
	public function get_add_alter_form() {

		return array (
			array('label'=>'Support Group name',
					'type'=>'text',
					'name'=>'supportgroupgroupname',
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'Contact Email',
					'type'=>'text',
					'name'=>'supportgroupgroupemail',
					'value_function'=>'get_email',
					'process_callback'=>'set_email')
		);
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT supportgroup_id FROM supportgroup WHERE supportgroup_id > 0';
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
			$sql .= ' ORDER BY supportgroup_name';
		}
		return $sql;
	}
	
	/**
	 * The method to return the HTML for the details on this specific host
	 */
	public function get_details() {
		$retval = '<table id="supportgroup_details">' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Support Group Name:</td><td>' . $this->get_name() . '</td></tr>' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Contact e-mail:</td><td>' . $this->get_email() . '</td></tr>' . "\n";
		$retval .= '</table>';
		return $retval;
	}

	public function get_displays() {
		return array('Name'=>'get_name','Contact e-mail'=>'get_email');
	}
	
	public function get_email() {
		return htmlspecialchars($this->email);
	}
	
	/**
	 * Get an array of all the hosts associated with this
	 * Support Group.  This is an expensive operation so it 
	 * isn't part of the constructor.
	 * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return array of host id's
	 */
	public function get_host_ids() {
		$sql = array(
			'SELECT host_id FROM host WHERE supportgroup_id = ?i',
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
     * Short description of method get_id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int
     */
    public function get_id() {
       return $this->id;
    }

    public function get_name() {
			return $this->name; 
    }

    public function save() {if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE supportgroup SET supportgroup_name = \'?s\', supportgroup_email = \'?s\' WHERE supportgroup_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_email(),
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO supportgroup SET supportgroup_name = \'?s\', supportgroup_email = \'?s\'',
    			$this->get_name(),
    			$this->get_email()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

    public function set_email($email) {
    	if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
			$this->email = $email;
    	}
		else {
  			// Invalid e-mail address
  			$this->log->write_error('Illegal e-mail address specified, class.Supportgroup.php');
  			$this->email = '';
		}
    }

    public function set_name($name) {
    	if ($name != '')
    		$this->name = htmlspecialchars($name);
    	elseif ($name == '')
    		$this->name = '';
    }

} /* end of class Supportgroup */

?>