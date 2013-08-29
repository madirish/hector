<?php
/**
 * HECTOR - class.Supportgroup.php
 *
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */

/**
 * Error reporting
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
 * Support group is an object that refers to the organizational
 * unit responsible for supporting a particular host or hosts.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Supportgroup extends Maleable_Object implements Maleable_Object_Interface {
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
     * @access protected
     * @var Int The unique ID from the data layer
     */
    protected $id = null;

	/**
	 * Name of the Support group
	 * 
	 * @access private
	 * @var String The name of the support group
	 */
    private $name;
    
    /**
     * The contact e-mail for the group
     * 
     * @access private
     * @var String The e-mail address for the support group
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
	 * @access public
	 * @var Array Array of host_id's for the Supportgroup
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
				$this->set_id($result[0]->supportgroup_id);
				$this->set_name($result[0]->supportgroup_name);
				$this->set_emai($result[0]->supportgroup_email);
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
	    		'DELETE FROM supportgroup WHERE supportgroup_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	if($retval = $this->db->iud_sql($sql)) {
		    	$sql = array(
		    		'DELETE FROM user_x_supportgroup WHERE supportgroup_id = \'?i\'',
		    		$this->get_id()
		    	);
		    	$retval = $this->db->iud_sql($sql);
	    	}
	    	$this->set_id(null);
    	}
    	return $retval;
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a tag.
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Array An array to suppor the standard CRUD template
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
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return String SQL select string
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
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String HTML for the display template
     * @todo Move this HTML into a template and out of the class.
	 */
	public function get_details() {
		$retval = '<table id="supportgroup_details">' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Support Group Name:</td><td>' . $this->get_name() . '</td></tr>' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Contact e-mail:</td><td>' . $this->get_email() . '</td></tr>' . "\n";
		$retval .= '</table>';
		return $retval;
	}
	
	/**
	 * Return the display array for the default display template.
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String HTML for the display template
	 */
	public function get_displays() {
		return array('Name'=>'get_name','Contact e-mail'=>'get_email');
	}
		
	/**
	 * Return the support group e-mail
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The e-mail contact for the Supportgroup
	 */
	public function get_email() {
		return filter_var($this->email, FILTER_SANITIZE_EMAIL);
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
	 * Return the support group name
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The HTML display safe name of the Supportgroup
	 */
    public function get_name() {
			return htmlspecialchars($this->name); 
    }
    
	/**
	 * Persist the Supportgroup to the data layer
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
	 */
    public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE supportgroup ' .
	    			'SET supportgroup_name = \'?s\', ' .
	    			'supportgroup_email = \'?s\' ' .
	    			'WHERE supportgroup_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_email(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO supportgroup ' .
					'SET supportgroup_name = \'?s\', ' .
					'supportgroup_email = \'?s\'',
    			$this->get_name(),
    			$this->get_email()
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
	 * Validate and set the Supportgroup e-mail
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param String The e-mail address of the Supportgroup
     * @return Boolean False if the address doesn't validate
	 */
    public function set_email($email) {
    	$retval = FALSE;
    	if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
			$this->email = $email;
			$retval = TRUE;
    	}
		else {
  			// Invalid e-mail address
  			$this->log->write_error('Illegal e-mail address specified, class.Supportgroup.php');
  			$this->email = '';
		}
		return $retval;
    }

	/**
	 * Set the name of the Supportgroup
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param The name of the Supportgroup
	 */
    public function set_name($name) {
    	$this->name = $name;
    }

} /* end of class Supportgroup */

?>