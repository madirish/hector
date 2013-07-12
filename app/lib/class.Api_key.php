<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Api_key.php
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
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/**
 * API keys allow acces to APIs'.
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Api_key extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute id
     *
     * @access private
     * @var int
     */
    protected $id = null;

	/**
	 * Holder name
	 * 
	 * @var String
	 */
	private $key_resource;
    private $holder_name;
	private $holder_affiliation;
	private $holder_email;
	private $key_value;


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
				'SELECT * FROM api_key WHERE api_key_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->api_key_id;
			$this->key_value = $result[0]->api_key_value;
			$this->key_resource = $result[0]->api_key_resource;
			$this->holder_name = $result[0]->api_key_holder_name;
			$this->holder_affiliation = $result[0]->api_key_holder_affiliation;
			$this->holder_email = $result[0]->api_key_holder_email;
			
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
    		$sql=array('Delete FROM api_key WHERE api_key_id =?i',
    			$this->get_id()
    		);
    		$this->db->iud_sql($sql);
    	}
    }
    
	/**
	 * This is a functional method designed to return
	 * the form associated with altering api_key information.
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
	 * @return SQL select string
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

	public function get_displays() {
		return array('Id'=>'get_id',
			'Value'=>'get_key_value',
			'Resource'=>'get_key_resource',
			'Holder name'=>'get_holder_name',
			'Holder affiliation'=>'get_holder_affiliation',
			'Holder email'=>'get_holder_email'
		);
	}


    public function get_id()
    {
       return $this->id;
    }

    public function get_holder_name() {
		return $this->holder_name;
    }
    
    public function get_holder_affiliation() {
		return $this->holder_affiliation;
    }
    
    public function get_holder_email() {
		return $this->holder_email;
    }
    
    public function get_key_resource() {
		return $this->key_resource;
    }
    
    public function get_key_value() {
		return $this->key_value;
    }
	public function new_key_value() {
        $key = time();
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 12; $i++) 
        {
            $key.= $characters[rand(0, strlen($characters))];
        }
        return sha1($key); 
    }
    public function save() {if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE api_key SET api_key_resource = \'?s\', api_key_holder_name = \'?s\', api_key_holder_affiliation = \'?s\', api_key_holder_email = \'?s\' WHERE api_key_id = \'?i\'',
	    		$this->get_key_resource(),
	    		$this->get_holder_name(),
	    		$this->get_holder_affiliation(),
	    		$this->get_holder_email(),
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO api_key SET api_key_value = \'?s\', api_key_resource = \'?s\', api_key_holder_name = \'?s\', api_key_holder_affiliation = \'?s\', api_key_holder_email = \'?s\'',
    			$this->new_key_value(),
    			$this->get_key_resource(),
	    		$this->get_holder_name(),
	    		$this->get_holder_affiliation(),
	    		$this->get_holder_email()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

    public function set_holder_name($holder_name) {
    	if ($holder_name != '')
    		$this->holder_name = htmlspecialchars($holder_name);
    	elseif ($holder_name == '')
    		$this->holder_name = '';
    }
    
    public function set_holder_affiliation($holder_affiliation) {
    	if ($holder_affiliation != '')
    		$this->holder_affiliation = htmlspecialchars($holder_affiliation);
    	elseif ($holder_affiliation == '')
    		$this->holder_affiliation = '';
    }
    
     public function set_holder_email($holder_email) {
    	if ($holder_email != '')
    		$this->holder_email = htmlspecialchars($holder_email);
    	elseif ($holder_email == '')
    		$this->holder_email = '';
    }
    
     public function set_key_resource($key_resource) {
    	if ($key_resource != '')
    		$this->key_resource = htmlspecialchars($key_resource);
    	elseif ($key_resource == '')
    		$this->key_resource = '';
    }

} /* end of class Api_key */

?>