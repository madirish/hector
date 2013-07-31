<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Vuln_details.php
 *
 * @author Josh Bauer <joshbauer3@gmail.com>
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
 * Occurances of Vulnerabilities.
 *
 * @access public
 * @author Josh Bauer <joshbauer3@gmail.com>
 */
class Vuln_details extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---

    /**
     * vuln_details_id
     *
     * @access private
     * @var int
     */
    protected $id = null;

	/**
	 * vuln_details_text
	 * 
	 * @var String
	 */
	private $text;
	
	/**
	 * logged datetime
	 * 
	 * @var String
	 */
    private $datetime;
    
    /**
	 * ignore
	 * 
	 * @var Boolean
	 */
	private $ignore;
	
	/**
	 * fixed
	 * 
	 * @var Boolean
	 */
	private $fixed;
	
	/**
	 * fixed datetime
	 * 
	 * @var String
	 */
	private $fixed_datetime;
	
	/**
	 * fixed notes
	 * 
	 * @var String
	 */
	private $fixed_notes;

    /**
     * vuln_id
     *
     * @access private
     * @var int
     */
    private $vuln_id = null;
    
    /**
     * host_id
     *
     * @access private
     * @var int
     */
    private $host_id = null;
    
    /**
     * host_name
     *
     * @access private
     * @var int
     */
    private $host_name = null;
    
    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @param  int id
     * @return void
     */
    public function __construct($id = '')
    {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM vuln_details WHERE vuln_details_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->vuln_details_id;
			$this->text = $result[0]->vuln_details_text;
			$this->datetime = $result[0]->vuln_details_datetime;
			$this->ignore = $result[0]->vuln_details_ignore;
			$this->fixed = $result[0]->vuln_details_fixed;
			$this->fixed_datetime = $result[0]->vuln_details_fixed_datetime;
			$this->fixed_notes = $result[0]->vuln_details_fixed_notes;
			$this->vuln_id = $result[0]->vuln_id;
			$sql = array(
				'SELECT v.host_id, h.host_name FROM vuln_x_host v inner join host h on h.host_id=v.host_id WHERE v.vuln_details_id =?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->host_id = $result[0]->host_id;
			$this->host_name = $result[0]->host_name;	
		}
    }

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @return void
     */
    public function delete() {
    	if ($this->id > 0 ) {
    		$sql=array('Delete FROM vuln_details WHERE vuln_details_id =?i',
    			$this->get_id()
    		);
    		$this->db->iud_sql($sql);
    	}
    }
    
	/**
	 * This is a functional method designed to return
	 * the form associated with altering vuln_details information.
	 */
	public function get_add_alter_form() {

		return array (
			array('label'=>'Text',
					'type'=>'text',
					'name'=>'resource_name',
					'value_function'=>'get_text',
					'process_callback'=>'set_text'),
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
		$sql = 'SELECT vd.vuln_details_id FROM vuln_details vd WHERE vd.vuln_details_id > 0';
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
			$sql .= ' ORDER BY vd.vuln_details_id';
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

	public function get_datetime() {
		return $this->datetime;
    }
    
    public function get_fixed() {
		return $this->fixed;
    }
    
    public function get_fixed_datetime() {
		return $this->fixed_datetime;
    }
    
    public function get_fixed_notes() {
       return $this->fixed_notes;
    }
    
    public function get_host_id() {
		return $this->host_id;
    }
    
    public function get_host_name() {
		return $this->host_name;
    }
    
    public function get_id() {
		return $this->id;
    }
      
    public function get_ignore() {
		return $this->ignore;
    }
    
    public function get_text() {
		return $this->text;
    }
    
    public function get_vuln_id() {
		return $this->vuln_id;
    }
    
    public function save() {if ($this->id > 0 ) {
    		// Update an existing vuln_detail
	    	$sql = array(
	    		'UPDATE vuln_details SET vuln_details_text = \'?s\', vuln_details_datetime = \'?s\', vuln_details_ignore = \'?s\', vuln_details_fixed = \'?s\', vuln_details_fixed_datetime = \'?s\', vuln_details_fixed_notes =\'?s\' WHERE vuln_details_id = \'?i\'',
				$this->get_text(),
    			$this->get_datetime(),
	    		$this->get_ignore(),
	    		$this->get_fixed(),
	    		$this->get_fixed_datetime(),
	    		$this->get_fixed_notes(),
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO vuln_details SET vuln_details_text = \'?s\', vuln_details_datetime = \'?s\', vuln_details_ignore = \'?s\', vuln_details_fixed = \'?s\', vuln_details_fixed_datetime = \'?s\', vuln_details_fixed_notes =\'?s\'',
    			$this->get_text(),
    			$this->get_datetime(),
	    		$this->get_ignore(),
	    		$this->get_fixed(),
	    		$this->get_fixed_datetime(),
	    		$this->get_fixed_notes()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
    
	public function set_datetime($datetime) {
    	if ($datetime != '')
    		$this->datetime = htmlspecialchars($datetime);
    	elseif ($datetime == '')
    		$this->datetime = '';
    }
    
     public function set_fixed($fixed) {
    	if ($fixed != '')
    		$this->fixed = htmlspecialchars($fixed);
    	elseif ($fixed == '')
    		$this->fixed = '';
    }
    
    public function set_fixed_datetime($fixed_datetime) {
    	if ($fixed_datetime != '')
    		$this->fixed_datetime = htmlspecialchars($fixed_datetime);
    	elseif ($fixed_datetime == '')
    		$this->fixed_datetime = '';
    }
    
     public function set_fixed_notes($fixed_notes) {
    	if ($fixed_notes != '')
    		$this->fixed_notes = htmlspecialchars($fixed_notes);
    	elseif ($fixed_notes == '')
    		$this->fixed_notes = '';
    }
    
    public function set_ignore($ignore) {
    	if ($ignore != '')
    		$this->ignore = htmlspecialchars($ignore);
    	elseif ($ignore == '')
    		$this->ignore = '';
    }
    
    public function set_text($text) {
    	if ($text != '')
    		$this->text = htmlspecialchars($text);
    	elseif ($text == '')
    		$this->text = '';
    }
    
    public function set_vuln_id($vuln_id) {
    	if ($vuln_id != '')
    		$this->vuln_id = htmlspecialchars($vuln_id);
    	elseif ($vuln_id == '')
    		$this->vuln_id = '';
    }

} /* end of class Vuln_details */

?>