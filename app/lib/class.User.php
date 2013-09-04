<?php
/**
 * HECTOR - class.User.php
 *
 *
 * This file is part of HECTOR
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @version .1
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
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');
require_once('class.Collection.php');
require_once('class.Db.php');
require_once('class.Log.php');

/**
 * User is the base object for user accounts and access.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @version .1
 */
class User extends Maleable_Object implements Maleable_Object_Interface {
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The username (for login)
     *
     * @access public
     * @var String
     */
    private $name = null;

    /**
     * Id of the corresponding person object.
     *
     * @access public
     * @var int
     */
    private $person_id = 0;
    
    /**
     * Unique id for the persistence layer.
     * 
     * @access protected
     * @var int
     */
    protected $id;
    
    private $is_admin = 0;
    
    // Array of support group ids
    private $supportgroup_ids = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
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
					'SELECT * FROM user WHERE user_id = ?i',
					$id
				);
				$result = $this->db->fetch_object_array($sql);
				$this->id = $result[0]->user_id;
				$this->name = $result[0]->user_name;
				$this->is_admin = $result[0]->user_is_admin;
				// Set up support groups
				$sql = array(
					'select supportgroup_id from user_x_supportgroup where user_id = ?i',
					$id);
				$result = $this->db->fetch_object_array($sql);
				if (is_array($result)) {
					foreach($result as $row) {
						$this->supportgroup_ids[] = $row->supportgroup_id;
					}
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
    		// Delete an existing user
	    	$sql = array(
	    		'DELETE FROM user WHERE user_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
	
	public function get_add_alter_form() {
		// get the Support groups array
		$supportgroups = array();
		$collection = new Collection('Supportgroup');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$supportgroups[$element->get_id()]=$element->get_name();
			}
		}
		return array (
			array('label'=>'User name', 
					'type'=>'text', 
					'name'=>'username', 
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'User password', 
					'name'=>'password', 
					'type'=>'password',
					'process_callback'=>'set_password'),
			array('label'=>'Admin user?', 
					'name'=>'is_admin_user', 
					'type'=>'select', 
					'options'=>array(0=>'No',1=>'Yes'), 
					'value_function'=>'get_is_admin',
					'process_callback'=>'set_is_admin'),
			array('label'=>'Support Group',
					'name'=>'supportgroup[]',
					'type'=>'checkbox',
					'options'=>$supportgroups,
					'value_function'=>'get_supportgroup_ids',
					'process_callback'=>'set_supportgroup_ids'),
		);
	}
	
	/**
	 * Look up the user id by their name.  This
	 * function is used in conjunction with CoSign
	 * for external authentication.
	 * 
	 * @param string $name
	 * @return void
	 */
	public function get_by_name($name) {
		$sql = array(
				'SELECT * FROM user WHERE user_name = \'?s\'',
				$name);
		$result = $this->db->fetch_object_array($sql); 
		if (is_array($result) && is_object($result[0])){  
			$this->id = $result[0]->user_id;
			$this->name = $result[0]->user_name;
			$this->is_admin = $result[0]->user_is_admin;
		}
	}
    
    /* This function directly supports the Collection class.
	 * 
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT u.user_id FROM user u WHERE u.user_id > 0';
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
		return array('User name'=>'get_name', 'Is Admin?'=>'get_is_admin_readable');
	}
	
	public function get_id() {
		return (isset($this->id)) ? $this->id : null;
	}
	
	public function get_is_admin() {
		return (int) $this->is_admin;
	}
	
	public function get_is_admin_readable()  {
		$retval = 'No';
		if ((int) $this->is_admin > 0 ) $retval = 'Yes';
		return $retval;
	}

	    /**
	 * Short description of method get_name
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_name() {
	    return $this->name;
	}
	
	/**
	 * Short description of method get_person_id
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return int
	 */
	public function get_person_id() {
	    $returnValue = (int) 0;
	
	    // section 127-0-0-1-6ee0c3aa:1262303b691:-8000:0000000000001000 begin
	    // section 127-0-0-1-6ee0c3aa:1262303b691:-8000:0000000000001000 end
	
	    return (int) $returnValue;
	}
	
	public function get_supportgroup_ids() {
		return $this->supportgroup_ids;
	}
	
	public function set_is_admin($val) {
		$this->is_admin = (intval($val) < 1) ? 0 : 1;
	}
	
	/**
	 * Short description of method set_name
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param  name
	 * @return void
	 */
	public function set_name($name) {
		$this->name = htmlspecialchars($name);
	}
     
	/**
	 * Set the support group id array
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  Array of Host_group ids
	 * @return void
	 */
	public function set_supportgroup_ids($array) {
		// Reset the array to clean out cruft
		$this->supportgroup_ids = array();
		// Put the new valus in
		foreach($array as $key=>$val) 
			$this->supportgroup_ids[] = intval($val);
	}
	
	/**
	 * Short description of method set_password
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param  password
	 * @return void
	 */
	public function set_password($password) {
	    $this->password = crypt($password);
	}
	
	public function save() {
		if ($this->id > 0 ) {
			// Update an existing user
	    	$sql = array(
	    		'UPDATE user SET user_name = \'?s\', user_pass = \'?s\', user_is_admin = \'?i\' WHERE user_id = \'?i\'',
	    		$this->get_name(),
	    		$this->password,
	    		$this->get_is_admin(),
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
		}
		else {
			$sql = array(
				'INSERT INTO user SET user_name = \'?s\', user_pass = \'?s\', user_is_admin = \'?i\'',
				$this->get_name(),
	    		$this->password,
	    		$this->get_is_admin()
	    	);
	    	$this->db->iud_sql($sql);
	    	// Set the uid for support group assignment
	    	$sql = array('SELECT user_id FROM user where user_name = \'?s\' AND user_pass = \'?s\'',
	    		$this->get_name(),
	    		$this->password
	    	);
				$result = $this->db->fetch_object_array($sql);
				$this->id = $result[0]->user_id;
		}
		// Update the support groups (if any)
		$sql = array(
			'DELETE FROM user_x_supportgroup WHERE user_id = ?i',
			$this->get_id()
		);
		$this->db->iud_sql($sql);
		if (is_array($this->get_supportgroup_ids()) && count($this->get_supportgroup_ids()) > 0) {
			foreach ($this->get_supportgroup_ids() as $gid) {
				$sql = array('INSERT INTO user_x_supportgroup SET user_id = ?i, supportgroup_id = ?i',
				$this->get_id(),
				$gid);
				$this->db->iud_sql($sql);
			}
		}
	}
	
	/**
	 * Short description of method validate
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param username
	 * @param password
	 * @return boolean
	 */
	public function validate($username, $password) {
		$retval = false;
		// Get the database password so we have the salt
		$sql = array('SELECT user_id, user_pass FROM user where user_name=\'?s\'', $username);
		$result = $this->db->fetch_object_array($sql);
		if (isset($result[0])) {
			$salt = $result[0]->user_pass;
		}
		else {
			return false;
		}
		
		$sql = array(
			'SELECT user_id FROM user WHERE user_name = \'?s\' AND user_pass = \'?s\'',
			$username,
			crypt($password, $salt));
		$result = $this->db->fetch_object_array($sql);
		if (isset($result[0]) && $result[0]->user_id > 0) {
			$this->__construct($result[0]->user_id);
			$retval = true;
		}
		return $retval;
	}

} /* end of class User */

?>