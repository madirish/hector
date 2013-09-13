<?php
/**
 * HECTOR - class.Api_key.php
 *
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Set up error reporting
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
 * API keys allow access to export feeds
 *
 * @access public
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
class Api_key extends Maleable_Object implements Maleable_Object_Interface {
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
     * Unique Id from the data layer
     *
     * @access protected
     * @var Int Unique ID from the data layer
     */
    protected $id = null;

	/**
	 * Key resource
	 * 
	 * @access private
	 * @var String The resource URL this key protects
	 */
	private $key_resource;
	
	/**
	 * Holder name
	 * 
	 * @var String The name of the principle the key was assigned to
	 */
    private $holder_name;
    
    /**
	 * Holder affiliation
	 * 
	 * @var String The holder's affiliation
	 */
	private $holder_affiliation;
	
	/**
	 * Holder email
	 * 
	 * @var String The email address to contact holder of the key
	 */
	private $holder_email;
	
	/**
	 * Key value
	 * 
	 * @var String The hash value of the key
	 */
	private $key_value;


    // --- OPERATIONS ---

    /**
     * Create a new instance of the Api_key
     *
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
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
				'SELECT * FROM api_key WHERE api_key_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0])) { // Ensure the id is valid
				$this->set_id($result[0]->api_key_id);
				$this->key_value = $result[0]->api_key_value; // no setter, internal only
				$this->set_key_resource($result[0]->api_key_resource);
				$this->set_holder_name($result[0]->api_key_holder_name);
				$this->set_holder_affiliation($result[0]->api_key_holder_affiliation);
				$this->set_holder_email($result[0]->api_key_holder_email);
			}
			
		}
    }

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		$sql=array('Delete FROM api_key WHERE api_key_id =?i',
    			$this->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
    }
    
	/**
	 * This is a functional method designed to return
	 * the form associated with altering api_key information.
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array The Array for supporting default CRUD template
	 */
	public function get_add_alter_form() {

		return array (
			array('label'=>'Resource name',
					'type'=>'text',
					'name'=>'resource_name',
					'value_function'=>'get_key_resource',
					'process_callback'=>'set_key_resource'),
			array('label'=>'Holder name',
					'type'=>'text',
					'name'=>'holder_name',
					'value_function'=>'get_holder_name',
					'process_callback'=>'set_holder_name'),
			array('label'=>'Holder affiliation',
					'type'=>'text',
					'name'=>'holder_affiliation',
					'value_function'=>'get_holder_affiliation',
					'process_callback'=>'set_holder_affiliation'),
			array('label'=>'Holder email',
					'type'=>'text',
					'name'=>'holder_email',
					'value_function'=>'get_holder_email',
					'process_callback'=>'set_holder_email')
		);
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String SQL select string to build the Collection
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT a.api_key_id FROM api_key a WHERE a.api_key_id > 0';
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
			$sql .= ' ORDER BY a.api_key_id';
		}
		return $sql;
	}

	/**
	 * Get the displays for default overview template
	 * 
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Array The array for default overview template
	 */
	public function get_displays() {
		return array('Id'=>'get_id',
			'Value'=>'get_key_value',
			'Resource'=>'get_key_resource',
			'Holder name'=>'get_holder_name',
			'Holder affiliation'=>'get_holder_affiliation',
			'Holder email'=>'get_holder_email'
		);
	}

	/**
	 * Get the holder affiliation
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The HTML display safe affiliation of the key holder
	 */
	public function get_holder_affiliation() {
		return htmlspecialchars($this->holder_affiliation);
    }

	/**
	 * Get the holder email
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The HTML display safe contact email of the key holder
	 */
    public function get_holder_email() {
		return filter_var($this->holder_email, FILTER_SANITIZE_EMAIL);
    }

	/**
	 * Get the holder name
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The HTML display safe name of the key holder
	 */
    public function get_holder_name() {
		return htmlspecialchars($this->holder_name);
    }

	/**
	 * Get the resource to which this key allows access
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The HTML display safe resource this key grants access to
	 */
    public function get_key_resource() {
		return htmlspecialchars($this->key_resource);
    }

	/**
	 * Get the actual key
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The HTML display safe key value
	 */
    public function get_key_value() {
		return htmlspecialchars($this->key_value);
    }

	/**
	 * Generate a new key
	 * 
	 * @access private
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @return String The SHA1 hash value of the new key
	 */
	private function new_key_value() {
        $key = time();
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 12; $i++)  {
            $key.= $characters[rand(0, strlen($characters)-1)];
        }
        return sha1($key); 
    }

	/**
	 * Persist the Api_key to the database
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
	 */
    public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing api key
	    	$sql = array(
	    		'UPDATE api_key SET api_key_resource = \'?s\', ' .
	    			'api_key_holder_name = \'?s\', ' .
	    			'api_key_holder_affiliation = \'?s\', ' .
	    			'api_key_holder_email = \'?s\' ' .
	    		'WHERE api_key_id = \'?i\'',
	    		$this->get_key_resource(),
	    		$this->get_holder_name(),
	    		$this->get_holder_affiliation(),
	    		$this->get_holder_email(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		// Set the internal key value
    		$this->key_value = $this->new_key_value();
    		$sql = array(
				'INSERT INTO api_key SET api_key_value = \'?s\', ' .
					'api_key_resource = \'?s\', ' .
					'api_key_holder_name = \'?s\', ' .
					'api_key_holder_affiliation = \'?s\', ' .
					'api_key_holder_email = \'?s\'',
    			$this->key_value,
    			$this->get_key_resource(),
	    		$this->get_holder_name(),
	    		$this->get_holder_affiliation(),
	    		$this->get_holder_email()
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
     * Set the key holder affiliation
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The affiliation of the key holder
     */
	public function set_holder_affiliation($holder_affiliation) {
    	$this->holder_affiliation = $holder_affiliation;
    }
    
    /**
     * Set the key holder email
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The email address of the key holder
     * @return Boolean False if e-mail is invalid
     */
	public function set_holder_email($holder_email) {
		$retval = FALSE;
		if (filter_var($holder_email, FILTER_VALIDATE_EMAIL)) {
			$this->holder_email = $holder_email;
			$retval = TRUE;
		}
    	return $retval;
    }
    
    /**
     * Set the key holder name
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The name of the key holder
     */
    public function set_holder_name($holder_name) {
    		$this->holder_name = $holder_name;
    }
    
    /**
     * Set the key resource
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The resource to which the key applies
     */
    public function set_key_resource($key_resource) {
    	$this->key_resource = $key_resource;
    }
    
    /**
     * Validate the key
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The key value
     * @return Boolean True if the key validates, False otherwise.
     * @todo Validate the resource as a second parameter
     */
    public function validate($key) {
    	$retval = FALSE;
    	if ($key != '') {
    		$sql=array('SELECT * FROM api_key WHERE api_key_value=\'?s\'',$key); 
    		$result=$this->db->fetch_object_array($sql); 
    		if (isset($result[0]) && intval($result[0]->api_key_id) !== 0) {
    			$retval = TRUE;
    		}
    	}
    	return $retval;
    }

} /* end of class Api_key */

?>