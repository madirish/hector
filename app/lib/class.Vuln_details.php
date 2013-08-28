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
     * array of vuln_details_id's
     * that match this vulnerability
     *
     * @access private
     * @var array
     */
    protected $ids = null;

	/**
	 * vuln_details_text
	 * 
	 * @var String
	 */
	private $text;
	
	/**
	 * first logged datetime
	 * 
	 * @var String
	 */
    private $first_datetime;
    
    /**
	 * last logged datetime
	 * 
	 * @var String
	 */
    private $last_datetime;
    
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
     * vuln_name
     *
     * @access private
     * @var int
     */
    private $vuln_name;
    
    /**
     * vuln_description
     *
     * @access private
     * @var int
     */
    private $vuln_description;
    
    /**
     * vuln_cve
     *
     * @access private
     * @var int
     */
    private $vuln_cve;
    
    /**
     * vuln_cve
     *
     * @access private
     * @var int
     */
    private $vuln_osvdb;
    
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
				'SELECT vd.*, v.vuln_name, v.vuln_description, v.vuln_cve, v.vuln_osvdb, vh.host_id, h.host_name FROM vuln_details vd ' .
				'inner join vuln v on v.vuln_id = vd.vuln_id ' .
				'inner join vuln_details_x_host vh on vh.vuln_details_id = vd.vuln_details_id ' . 
				'inner join host h on h.host_id = vh.host_id WHERE vd.vuln_details_id =?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->vuln_details_id;
			$this->text = $result[0]->vuln_details_text;
			$this->ignore = $result[0]->vuln_details_ignore;
			$this->fixed = $result[0]->vuln_details_fixed;
			$this->fixed_datetime = $result[0]->vuln_details_fixed_datetime;
			$this->fixed_notes = $result[0]->vuln_details_fixed_notes;
			$this->vuln_id = $result[0]->vuln_id;
			$this->vuln_name = $result[0]->vuln_name;
			$this->vuln_description = $result[0]->vuln_description;
			$this->vuln_cve = $result[0]->vuln_cve;
			$this->vuln_osvdb = $result[0]->vuln_osvdb;
			$result = $this->db->fetch_object_array($sql);
			$this->host_id = $result[0]->host_id;
			$this->host_name = $result[0]->host_name;
			
			$ids = array();
			$sql = array(
				'SELECT vd.vuln_details_id from vuln_details vd ' .
				'inner join vuln_details_x_host vh on vh.vuln_details_id = vd.vuln_details_id ' . 
				'inner join host h on h.host_id = vh.host_id ' .
				'WHERE vh.host_id=?i and vd.vuln_id=?i and vd.vuln_details_text=\'?s\'',
				$this->host_id,
				$this->vuln_id,
				$this->text
				);
			$results = $this->db->fetch_object_array($sql);
			foreach($results as $result) $this->ids[]=$result->vuln_details_id;
			
			$sql = array(
				'SELECT max(vd.vuln_details_datetime) as last, min(vd.vuln_details_datetime) as first from vuln_details vd ' .
				'WHERE vd.vuln_details_id in (?s)',
				implode(',', $this->ids)
				);
			$result = $this->db->fetch_object_array($sql);
			$this->first_datetime = $result[0]->first;
			$this->last_datetime = $result[0]->last;
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
	 * 
	 * NOT USED IN THIS CLASS
	 *
	 */
	public function get_add_alter_form() {
		return array ();
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
	
	/**
	 * get_displays() is not used.
	 */
	public function get_displays() {
		return array();
	}

	public function get_first_datetime() {
		return $this->first_datetime;
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
    
    public function get_last_datetime() {
		return $this->last_datetime;
    }
    
    public function get_text() {
		return $this->text;
    }
    
     public function get_vuln_cve() {
		return $this->vuln_cve;
    }
    
    public function get_vuln_description() {
		return $this->vuln_description;
    }
    
    public function get_vuln_id() {
		return $this->vuln_id;
    }
    
     public function get_vuln_name() {
		return $this->vuln_name;
    }
    
     public function get_vuln_osvdb() {
		return $this->vuln_osvdb;
    }
    
    public function save() {if ($this->id > 0 ) {
    		// Update an existing vuln_detail
	    	$sql = array(
	    		'UPDATE vuln_details SET vuln_details_text = \'?s\', vuln_details_ignore = \'?s\', vuln_details_fixed = \'?s\', vuln_details_fixed_datetime = \'?s\', vuln_details_fixed_notes =\'?s\' ' .
	    		'WHERE vuln_details_id in (?s)',
				$this->get_text(),
	    		$this->get_ignore(),
	    		$this->get_fixed(),
	    		$this->get_fixed_datetime(),
	    		$this->get_fixed_notes(),
	    		implode(',', $this->ids)
	    	);
	    	$this->db->iud_sql($sql);
    	}
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

} /* end of class Vuln_details */

?>