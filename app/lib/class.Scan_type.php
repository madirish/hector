<?php
/**
 * @package HECTOR
 * @author Justin C. Klein Keane <justin@madirish.net>
 * HECTOR - class.Scan_type.php
 *
 * class.Scan_type.php is the holder object for scans of 
 * various sorts so we can plug them into the Collection
 * factory class and display them via the web interface.
 *
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
 * Scan_type is the specific setup of a scan that includes not
 * only the scan script filename, but also configuration options
 * that are set.  This allows multiple scans of the same type to
 * be saved, with different configuration options.  Subsequently
 * Scan objects can be used to schedule each Scan_type.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @version .1
 */
class Scan_type extends Maleable_Object implements Maleable_Object_Interface {
    
    private $name;
    
    private $script;
    
    private $flags;
    
    private $onselects = null;
    
    // --- OPERATIONS ---

    /**
     * Constructor to set up a Scan_type object
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param  int id
     * @return void
     */
    public function __construct($id = '')
    {
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM scan_type WHERE scan_type_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->scan_type_id;
			$this->name = $result[0]->scan_type_name;
			$this->flags = $result[0]->scan_type_flags;
			$this->script = $result[0]->scan_type_script;
		}
    }

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     */
    public function delete() {
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM scan_type WHERE scan_type_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
    /**
     * Get the add/edit form for display in the template
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Array
     */	
	public function get_add_alter_form() {
		// get the host groups array
		$hostgroups = array();
		$collection = new Collection('Scan_type');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$hostgroups[$element->get_id()]=$element->get_name();
			}
		}
		$onselects = (is_null($this->onselects)) ? $this->get_script_exes() : $this->onselects;
		return array (
			array('label'=>'Scan type name', 
					'type'=>'text', 
					'name'=>'typename', 
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'Script', 
					'name'=>'script', 
					'type'=>'select',
					//'options'=>$this->get_script_exes(),
					'options'=>$this->exes,
					//'onselects'=>$this->get_script_onselects(),
					'onselects'=>$onselects,
					'value_function'=>'get_script',
					'process_callback'=>'set_script'),
			array('label'=>'', 
					'name'=>'flags', 
					'type'=>'hidden', 
					'value_function'=>'get_flags',
					'process_callback'=>'set_flags')
		);
	}
	
	/**
	 * This function sets up the script that goes in the footer
	 * template to populate the defaults for scripts in the add/edit
	 * form.
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String
	 */
	public function get_footer_scripts() {
		$onselects = (is_null($this->onselects)) ? $this->get_script_exes() : $this->onselects;
		$output = $this->onselects[$this->get_script()];
		if ($output == '') {
			$output = 'document.getElementById("nmap_scan.php").defaultSelected = true;';
			$output .= 'nmap_scan_display()';
		}
		return '<script type="text/javascript">' . $output . ';</script>';
	}
	
	/**
	 * Scan the scripts directory and populate the 
	 * get_add_alter_form() appropriately with only
	 * scripts meant to be configured via the web
	 * front end.  Note that only scripts that have 
	 * a filename ending in _scan.php will be included.
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Array
	 */
	private function get_script_exes() {
		global $approot;
		$onselects = array();
		$is_executable = array();
		if ($handle = opendir($approot . '/scripts')) {
			while (false !== ($entry = readdir($handle))) {
				$fname = $approot . 'scripts/' . $entry; 
				if (is_file($fname) && substr($fname, -9) == "_scan.php") {
					include_once($fname);
				}
			}
		}
		else {
			$this->log->write_error('Error reading the scripts/ directory from class.Scan_type.php');
		}
		foreach($is_executable as $script) {
			foreach ($script as $key=>$val) $retval[$key] = $val;
		} 
		$this->onselects = $onselects;
		$this->exes = $retval;
	}
	/**
     * Get a list of the onselect functions for the script
     * out of the script files.
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String
     */
	private function get_script_onselects() { 
		return $this->onselects;
	}
	
	
    /** 
     * This function directly supports the Collection class.
	 * 
	 * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT scan_type_id FROM scan_type WHERE scan_type_id > 0';
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
     * Get the values for display in the add/edit form
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     */
	public function get_displays() {
		return array('Name'=>'get_name', 'Script'=>'get_script', 'Flags'=>'get_flags');
	}
	/**
     * Get flags set on the script.
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The flags set up for the script.
     */
	public function get_flags() {
		return htmlspecialchars($this->flags);
	}
	
	public function get_id() {
		return (int) $this->id;
	}
	
	public function get_name() {
		return htmlspecialchars($this->name);
	}
	
	public function get_script() {
		return htmlspecialchars($this->script);
	}
	    
	/**
     * Persist the record to the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     */
	public function save() {
    	if ($this->id > 0 ) {
    		// Update an existing record
	    	$sql = array(
	    		'UPDATE scan_type SET scan_type_name = \'?s\', scan_type_flags = \'?s\', scan_type_script = \'?s\' WHERE scan_type_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_flags(),
	    		$this->get_script(),
	    		$this->id
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		// Insert a new value
	    	$sql = array(
	    		'INSERT INTO scan_type SET scan_type_name = \'?s\', scan_type_flags = \'?s\', scan_type_script = \'?s\'',
	    		$this->get_name(),
	    		$this->get_flags(),
	    		$this->get_script()
	    	);
	    	$this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = array(
	    		'SELECT scan_type_id FROM scan_type WHERE scan_type_name = \'?s\' AND scan_type_flags = \'?s\' AND scan_type_script = \'?s\'',
	    		$this->get_name(),
	    		$this->get_flags(),
	    		$this->get_script()
	    	);
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->scan_type_id > 0) {
	    		$this->set_id($result[0]->scan_type_id);
	    	}
    	}
	}
	
	public function set_flags($flags) {
		$this->flags = $flags;
	}
	
	public function set_name($name) {
		$this->name = $name;
	}
	
	public function set_script($script) {
		$this->script = escapeshellcmd($script);
	}
    
}
?>