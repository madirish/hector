<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Vuln_detail.php
 *
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @package HECTOR
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Vuln.php');
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
class Vuln_detail extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---

    /**
     * The unique ID from the database.
     *
     * @access private
     * @var int
     */
    protected $id = null;

	/**
	 * Text of the vulnerability detail, usually indicating 
	 * the specific circumstances of the vulnerability or 
	 * any additional details useful for remediation.
	 * 
	 * @access private
	 * @var String
	 */
	private $text;
    
    /**
	 * Boolean indicating that the vulnearbility should be 
	 * ignored (usually in case of mitigations).
	 * 
	 * @access private
	 * @var Boolean
	 */
	private $ignore;
	
	/**
	 * The timestamp of when the vulnerability was marked to 
	 * be ignored.
	 * 
	 * @access private
	 * @var datetime
	 */
	private $ignored_datetime;
	
	/**
	 * The unique ID, for creating a User object, of the user
	 * who makred the vulnerability for being ignored.
	 * 
	 * @access private
	 * @var int
	 */
	private $ignored_user_id;
	
	/**
	 * Boolean indicating whether or not the vulnerability has
	 * been fixed.  Default is to 0 (or false).
	 * 
	 * @access private
	 * @var Boolean
	 */
	private $fixed;
	
	/**
	 * The datetime when the vulnerability was marked as 
	 * fixed.
	 * 
	 * @access private
	 * @var String
	 */
	private $fixed_datetime;
	
	/**
	 * Notes entered by whomever fixed the vulnerability, to
	 * indicate steps taken or additional details.
	 * 
	 * @access private
	 * @var String Notes associated with the fix.
	 */
	private $fixed_notes;
	
	/** 
	 * Unique ID, for creating a User object, of the user who
	 * marked the vulnerability as fixed.
	 * 
	 * @access private
	 * @var int The user id for who marked fixed
	 */
	private $fixed_user_id;

    /**
     * Instance of the corresponding Vuln object
     *
     * @access private
     * @var Object Vuln 
     */
    private $vuln = null;
    
    /**
     * The unique ID of the assocaited Vulnerability, which
     * can be used to instantiate a Vuln object.
     * 
     * @access private
     * @var int The unique vuln id
     */
    private $vuln_id = 0;
    
    /**
     * The unique ID of the associated Host so that we can 
     * instantiate a Host object to get more details.
     *
     * @access private
     * @var int Unique host id
     */
    private $host_id = null;
    
    // --- OPERATIONS ---

    /**
     * Constructor method, to pull data about the object from 
     * the data layer, or construct the skeletal object so it
     * can later be persisted back to the data layer.
     * 
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @version 2013.08.29
     * @param  int Unique id for the Vuln_detail
     * @return void
     */
    public function __construct($id = '')
    {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * from vuln_detail where vuln_detail_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0])) {
				$r = $result[0];
				$this->id = $r->vuln_detail_id;
				$this->text = $r->vuln_detail_text;
				$this->ignore = $r->vuln_detail_ignore;
				$this->ignored_datetime = $r->vuln_detail_ignored_datetime;
				$this->ignored_user_id = $r->vuln_detail_ignoredby_user_id;
				$this->fixed = $r->vuln_detail_fixed;
				$this->fixed_datetime = $r->vuln_detail_fixed_datetime;
				$this->fixed_notes = $r->vuln_detail_fixed_notes;
				$this->fixed_user_id = $r->vuln_detail_fixedby_user_id;
				$this->vuln_id = $r->vuln_id;
				$this->host_id = $r->host_id;
			}
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
    		$sql=array('Delete FROM vuln_detail WHERE vuln_detail_id =?i',
    			$this->id
    		);
    		$this->db->iud_sql($sql);
    	}
    }
    
	/**
	 * This is a functional method designed to return
	 * the form associated with altering vuln_detail information.
	 * 
	 * NOT USED IN THIS CLASS
	 * 
	 * @access public
	 * @return Array A zero element array.
	 * @deprecated
	 */
	public function get_add_alter_form() {
		return array ();
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @access public
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT vuln_detail_id FROM vuln_detail WHERE vuln_detail_id > 0';
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
			$sql .= ' ORDER BY vuln_detail_datetime DESC';
		}
		return $sql;
	}
	
	/**
	 * get_displays() is not used.
	 */
	public function get_displays() {
		return array();
	}
    
    public function get_fixed() {
		return (bool) $this->fixed;
    }
    
    public function get_fixed_datetime() {
		return $this->fixed_datetime;
    }
    
    public function get_fixed_notes() {
       return htmlspecialchars($this->fixed_notes);
    }
    
    public function get_fixed_user_id() {
    	return intval($this->fixed_user_id);
    }
    
    public function get_host_id() {
		return intval($this->host_id);
    }
    
    public function get_host() {
		if ($this->host == NULL) {
			$this->host = new Host($this->host_id);
		}
		return $this->host;
    }
    
    public function get_id() {
		return intval($this->id);
    }
      
    public function get_ignore() {
		return (bool) $this->ignore;
    }
    
    public function get_ignore_datetime() {
    	return $this->ignore_datetime;
    }
    
    public function get_ignored_user_id() {
    	return intval($this->ignored_user_id);
    }
    
    public function get_text() {
		return htmlspecialchars($this->text);
    }
    
    public function get_vuln() {
    	if ($this->vuln == NULL) {
    		$this->vuln = new Vuln($this->vuln_id);
    	}
    	return $this->vuln;
    }
    
    public function save() {
    	if ($this->id > 0 ) {
    		// Update an existing vuln_detail
	    	$sql = array(
	    		'UPDATE vuln_detail SET ' .
	    			'vuln_detail_text = \'?s\', ' .
	    			'vuln_detail_ignore = \'?s\', ' .
	    			'vuln_detail_fixed = \'?s\', ' .
	    			'vuln_detail_fixed_datetime = \'?s\', ' .
	    			'vuln_detail_fixed_notes =\'?s\',' .
	    			'vunl_detail_ignore ' .
	    		'WHERE vuln_detail_id in (?s)',
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
     	$this->fixed = (bool) $fixed;
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
    	$this->ignore = (bool) $ignore;
    }
    
    public function set_text($text) {
    	if ($text != '')
    		$this->text = htmlspecialchars($text);
    	elseif ($text == '')
    		$this->text = '';
    }
    
    public function set_vuln_id($id) {
    	$this->vuln_id = intval($id);
    }

} /* end of class Vuln_detail */

?>