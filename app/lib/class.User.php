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
     * The username (for login)
     *
     * @access private
     * @var String The username for login
     */
    private $name = null;
    
    private $password = null;

    /**
     * The email address for communications and
     * password resets
     *
     * @access private
     * @var String The user's email address
     */
    private $email = null;
    
    /**
     * Unique id for the persistence layer.
     * 
     * @access protected
     * @var Int Unique ID from the data layer
     */
    protected $id = null;
    
    /**
     * Whether or not the user is admin
     * 
     * @access private
     * @var Booleane True if the user is an admin
     */
    private $is_admin = FALSE;
    
    /**
     * Array of support group ids
     * 
     * @access private
     * @var Array An array of the support groups to which this User belongs
     */ 
    private $supportgroup_ids = array();

    // --- OPERATIONS ---

    /**
     * Instantiate a new User object
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
				$this->set_id($result[0]->user_id);
				$this->set_name($result[0]->user_name);
				$this->set_email($result[0]->user_email);
				$this->set_is_admin($result[0]->user_is_admin);
				// Set up support groups
				$sql = array(
					'select supportgroup_id from user_x_supportgroup where user_id = ?i',
					$id);
				$result = $this->db->fetch_object_array($sql);
				if (is_array($result)) {
					$supportgroup_ids = array();
					foreach($result as $row) {
						$supportgroup_ids[] = $row->supportgroup_id;
					}
					$this->set_supportgroup_ids($supportgroup_ids);
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
    		// Delete an existing user
	    	$sql = array(
	    		'DELETE FROM user WHERE user_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	if ($retval) {
	    		$this->set_id(NULL);
	    	}
    	}
    	return $retval;
    }
    
    public function do_nothing() {}
	
	/**
	 * Return the Array for CRUD template
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Arry The array for the CRUD template.
	 */
	public function get_add_alter_form() {
		// get the Support groups array
		$supportgroups = array();
		$collection = new Collection('Supportgroup');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$supportgroups[$element->get_id()]=$element->get_name();
			}
		}
		$formarray = array (
			array('label'=>'User name', 
					'type'=>'text', 
					'name'=>'username', 
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'User password', 
					'name'=>'password', 
					'type'=>'password',
					'process_callback'=>'set_password'),
			array('label'=>'E-mail address', 
					'name'=>'email', 
					'type'=>'text',
					'value_function'=>'get_email',
					'process_callback'=>'set_email'),
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
		
		if ($this->id == null) {
			$confirm = array(array('label'=>'Confirm password', 
					'name'=>'password_confirm', 
					'type'=>'password',
					'process_callback'=>'do_nothing'
			));
			array_splice($formarray,2,0,$confirm);
		}
		
		return $formarray;
	}
	
	/**
	 * Look up the user id by their name.  This
	 * function is used in conjunction with CoSign
	 * for external authentication.
	 * 
	 * @param String The name of the user
	 * @return Boolean False if something goes awry or the lookup fails
	 */
	public function get_by_name($name) {
		$retval = FALSE;
		$sql = array(
				'SELECT * FROM user WHERE user_name = \'?s\'',
				$name);
		$result = $this->db->fetch_object_array($sql); 
		if (is_array($result) && isset($result[0]) && is_object($result[0])){  
			$this->set_id($result[0]->user_id);
			$this->set_name($result[0]->user_name);
			$this->set_is_admin($result[0]->user_is_admin);
		}
		if ($this->get_id() > 0) $retval = TRUE;
		return $retval;
	}
    
    /** 
     * This function directly supports the Collection class.
	 * 
	 * @access public
	 * @param String The optional additional SQL WHERE clause arguments
	 * @param String The optional SQL ORDER BY clause arguments
	 * @return String SQL select string
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
	
	/**
	 * Return an array for the default display template
	 * 
	 * @access public
	 * @return Array The Array for the default display template
	 */
	public function get_displays() {
		return array('User name'=>'get_name', 'Is Admin?'=>'get_is_admin_readable');
	}
		
	/**
	 * Return the user's e-mail
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <justin@madirish.net>
     * @return String The e-mail address for the user
	 */
	public function get_email() {
		return filter_var($this->email, FILTER_SANITIZE_EMAIL);
	}
	
	/**
	 * Return whether or not this user is an admin
	 * 
	 * @access public
	 * @return Boolean True if admin, False otherwise
	 */
	public function get_is_admin() {
		return (bool) $this->is_admin;
	}
	
	/**
	 * Return a String for whether or not this User is an admin
	 * 
	 * @access public
	 * @return String 'Yes' if admin, 'No' otherwise
	 */
	public function get_is_admin_readable()  {
		$retval = 'No';
		if ((boolean) $this->is_admin) $retval = 'Yes';
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
        return 'User';
    } 

	/**
	 * Get the HTML display safe user name
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return String The HTML display safe user name
	 */
	public function get_name() {
	    return htmlspecialchars($this->name);
	}
	
	/**
	 * Get an array of the Supportgroup IDs
	 * 
	 * @access public
	 * @return Array An Array of Supportgroup ids
	 */
	public function get_supportgroup_ids() {
		return $this->supportgroup_ids;
	}
	
	/**
	 * Set whether or not the person is an admin
	 * 
	 * @access spublic
	 * @param Boolean Whether or not the person is an admin
	 */
	public function set_is_admin($val) {
		$retval = true;
		// Is the caller an admin? Can they do this?
        global $appuser;
        if (! isset($appuser)) {
        	// $appuser isn't bootstrapped, this is likely an internal call (trust it)
        	$this->is_admin = (bool) $val;
        }
        elseif (isset($appuser) && $appuser->get_is_admin()) {
        	// Reset another user via the interface (done by an admin)
        	$this->is_admin = (bool) $val;
        }
        else {
        	// Attempt to reset a user account when not admin?
        	$this->log->write_error("Error in User::set_is_admin() by non-admin user (" . $appuser->get_id() . ")");
        	$retval = false;
        }
        return $retval;
	}
	
	/**
	 * Set the name of the user
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param  name
	 * @return void
	 */
	public function set_name($name) {
		$this->name = $name;
	}
	
	/**
	 * Add a new Supportgroup ID to the array
	 * 
	 * @access public
	 * @param Int The unique id of the Supportgroup
	 */
	public function set_add_supportgroup_id($id) {
		$retval = FALSE;
		$id = intval($id);
		// Make sure we get a valid id
		if ($id > 0) {
			// Don't insert dupes
			if (! array_search($id, $this->supportgroup_ids)) {
				$this->supportgroup_ids[] = $id;
				$retval = TRUE;
			}
		}
		return $retval;
	}
    
	/**
	 * Validate and set the User e-mail
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <justin@madirish.net>
     * @param String The e-mail address of the User
     * @return Boolean False if the address doesn't validate
	 */
    public function set_email($email) {
    	if ($email == NULL) {
    		$this->email = NULL;
    		return FALSE;
    	}
    	$retval = FALSE;
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->email = $email;
			$retval = TRUE;
		}
		else {
  			// Invalid e-mail address
  			$this->log->write_error('Illegal e-mail address specified, class.User.php');
  			$this->email = '';
		}
    	return $retval;
    }
     
	/**
	 * Reset the support group id array
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  Array The array of Supportgroup ids
	 * @return Boolean
	 */
	public function set_supportgroup_ids($array) {
		if (! is_array($array)) {
			$this->log->write_error("Invalid input passed to set_supportgroup_ids().");
			return false;
		}
		// validate the array
		foreach($array as $key=>$val) {
			if (! is_int($val)) {
				$this->log->write_error("Invalid element in supportgroup array.");
				return false;
			}
		}
		// Reset the array to clean out cruft
		$this->supportgroup_ids = array();
		// Put the new valus in
		foreach($array as $key=>$val) 
			$this->supportgroup_ids[] = intval($val);
		return true;
	}
	
	/**
	 * Set the user password
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param String The new password
	 * @return void
	 */
	public function set_password($password) {
		// Blank passwords come in as part of form processing
		// They indicate we don't want to update the password
		if ($password !== '') {
			$salt = sha1(time() + rand(0,1000));
		    $this->password = crypt($password, $salt);
		}
	}
	
	/**
	 * Persist the object to the data layer
	 * 
	 * @access public
	 * @return Boolean False if something goes awry
	 */
	public function save() {
		$retval = FALSE;
		// We don't show passwords, so a blank password
		// is only allowed for an update without a 
		// password change.
		if ($this->password == '' && intval($this->id) == 0) {
  			$this->log->write_error('Attempting to insert a user w/o a password');
			return FALSE;
		}
		if ($this->id > 0 ) {
  			$this->log->write_message('Updating user system id ' . $this->id);
	 		$sql = '';
	    	if ($this->password == '') {
	    		$sql = array(
		    		'UPDATE user SET user_name = \'?s\', ' .
		    			'user_is_admin = \'?i\', ' .
		    			'user_email = \'?s\' ' .
	    			'WHERE user_id = \'?i\'',
		    		$this->get_name(),
		    		$this->get_is_admin(),
		    		$this->get_email(),
		    		$this->get_id()
		    	);
	    	}
	    	else {
	    		$sql = array(
	    				'UPDATE user SET user_name = \'?s\', ' .
	    				'user_pass = \'?s\', ' .
	    				'user_is_admin = \'?i\', ' .
	    				'user_email = \'?s\' ' .
	    				'WHERE user_id = \'?i\'',
	    				$this->get_name(),
	    				$this->password,
	    				$this->get_is_admin(),
	    				$this->get_email(),
	    				$this->get_id()
	    		);
	    	}
	    	$retval = $this->db->iud_sql($sql);
		}
		else {
			$sql = array(
				'INSERT INTO user ' .
					'SET user_name = \'?s\', ' .
					'user_pass = \'?s\', ' .
					'user_email = \'?s\', ' .
					'user_is_admin = \'?i\'',
				$this->get_name(),
	    		$this->password,
				$this->get_email(),
	    		$this->get_is_admin()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->last_id > 0) {
	    		$this->set_id($result[0]->last_id);
	    	}
  			$this->log->write_message('Adding new user to the system id ' . $this->id);
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
		return $retval;
	}
	
	/**
	 * Validate a set of user credentials
	 *
	 * @access public
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param username
	 * @param password
	 * @return Boolean False if the username/password combo doens't validate
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