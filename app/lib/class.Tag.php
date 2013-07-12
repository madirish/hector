<?php

error_reporting(E_ALL);

/**
 * HECTOR - class.Tag.php
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
 * Tags are free taxonomies used to group hosts.
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Tag extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute id
     *
     * @access private
     * @var int
     */
    protected $id = null;

	/**
	 * Tag name
	 * 
	 * @var String
	 */
    private $name;

	/**
	 * Hosts associated with this tag.  This
	 * is just a convenience (for reporting).
	 * There is no interface for altering this
	 * attribute.
	 */
    public $host_ids = array();

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
				'SELECT * FROM tag WHERE tag_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->tag_id;
			$this->name = $result[0]->tag_name;
			$sql = array(
				'SELECT host_id FROM host_x_tag WHERE tag_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
	    	if (is_array($result) && count($result) > 0) {
	    		foreach($result as $row) {
	    			$this->host_ids[] = $row->host_id;
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
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM tag WHERE tag_id = \'?i\'',
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
			array('label'=>'Tag name',
					'type'=>'text',
					'name'=>'tagname',
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
		$sql = 'SELECT t.tag_id FROM tag t WHERE t.tag_id > 0';
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
			$sql .= ' ORDER BY t.tag_name';
		}
		return $sql;
	}

	public function get_displays() {
		return array('Name'=>'get_name');
	}

    /**
     * Short description of method get_id
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int
     */
    public function get_id()
    {
       return $this->id;
    }

    public function get_name() {
		return $this->name;
    }

    public function save() {if ($this->id > 0 ) {
    		// Update an existing user
	    	$sql = array(
	    		'UPDATE tag SET tag_name = \'?s\' WHERE tag_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO tag SET tag_name = \'?s\'',
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

} /* end of class Tag */

?>