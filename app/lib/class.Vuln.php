<?php
/**
 * HECTOR - class.Vuln.php
 *
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
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
 * Vulnerabilities
 *
 * @access public
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @package HECTOR
 */
class Vuln extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---

    /**
     * Unique id reflected from the databasse
     *
     * @access private
     * @var int
     */
    protected $id = null;

	/**
	 * Detailed description of the vulnerability.
	 * 
	 * @var String
	 */
	private $description;
	
	/**
	 * Vulnerability class description, such as
	 * "Default or easily guessed Credentials," or
	 * CVE-2012-0897
	 * 
	 * @var String
	 */
    private $name;
    	
	/**
	 * Mitre Common Vulnerability Enumerator (CVE)
	 * 
	 * @var String
	 */
	private $cve;
	
	/**
	 * Open Source Vulnerability Database (OSVDB) 
	 * designation.
	 * 
	 * @var String
	 */
	private $osvdb;
	
	/**
	 * Tag ids relating to the Vulnerability
	 * 
	 * @var Array
	 */
	private $tag_ids;

    // --- OPERATIONS ---

    /**
     * Generic constructor.  Look up the object
     * from the database or instantiate a blank
     * one.
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
				'SELECT * FROM vuln WHERE vuln_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (isset($result[0])) {
				$this->id = $result[0]->vuln_id;
				$this->name = $result[0]->vuln_name;
				$this->description = $result[0]->vuln_description;
				$this->cve = $result[0]->vuln_cve;
				$this->osvdb = $result[0]->vuln_osvdb;
			}
			$sql = array('SELECT tag_id FROM vuln_x_tag WHERE vuln_id = ?i',$id);
			$result = $this->db->fetch_object_array($sql);
			if (count($result) > 0) {
				require_once('class.Tag.php');
				foreach ($result as $item) {
					$this->add_tag_id($item->tag_id);
				}
			}
		}
    }

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @return Boolean True if the delete succeeds, False otherwise.
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		$sql=array('Delete FROM vuln WHERE vuln_id =?i',
    			$this->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	// Delete vuln_x_tag mapping for the vulnerability
    	$sql = array(
    			'DELETE FROM vuln_x_tag WHERE vuln_id = ?i',
    			$this->get_id()
    	);
    	$this->db->iud_sql($sql);
    	return $retval;
    }
    
	/**
	 * This is a functional method designed to return
	 * the form associated with altering vuln information.
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array Array for use with a Form object for display.
	 */
	public function get_add_alter_form() {

		return array (
			array('label'=>'Name',
					'type'=>'text',
					'name'=>'name',
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'Description',
					'type'=>'textarea',
					'name'=>'description',
					'value_function'=>'get_description',
					'process_callback'=>'set_description'),
			array('label'=>'CVE',
					'type'=>'text',
					'name'=>'cve',
					'value_function'=>'get_cve',
					'process_callback'=>'set_cve'),
			array('label'=>'OSVDB',
					'type'=>'text',
					'name'=>'osvdb',
					'value_function'=>'get_osvdb',
					'process_callback'=>'set_osvdb'),
			array('label'=>'Tags',
					'type'=>'text',
					'name'=>'tags',
					'process_callback' =>'process_tag_values',
					'value_function' => 'get_tag_names_callback')
		);
	}

    /**
     * This function directly supports the Collection class.
	 *
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$sql = 'SELECT v.vuln_id FROM vuln v WHERE v.vuln_id > 0';
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
			$sql .= ' ORDER BY v.vuln_name';
		}
		return $sql;
	}

	/**
	 * This helper function returns an array for display for 
	 * use with the generic display handler.
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array Formatted array for generic display.
	 */
	public function get_displays() {
		return array('Id'=>'get_id',
			'Name'=>'get_name',
			'Description'=>'get_description',
			'CVE'=>'get_cve',
			'OSVDB'=>'get_osvdb'
		);
	}

	/**
	 * Return the sanitized CVE designator.
	 * 
	 * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String CVE designation
	 */
	public function get_cve() {
		return htmlspecialchars($this->cve);
    }
    
    /**
     * Return the display safe description of the vulnerability.
     * 
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The vulnerability description
     */
    public function get_description() {
        global $approot;
        require_once($approot . '/software/htmlpurifier/library/HTMLPurifier.auto.php');
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Attr.EnableID', true);
        $purifier = new HTMLPurifier($config);
		return $purifier->purify($this->description);
    }
    
    /**
     * Return the unique id from the data layer
     * 
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return int Unique id from the data layer or zero
     */
    public function get_id() {
       return intval($this->id);
    }
    
    /**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Vulnerability';
    } 
    
    /**
     * Return the display safe name
     * 
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The vulnerability name
     */
    public function get_name() {
		return htmlspecialchars($this->name);
    }
    
    /**
     * Return the vulnerability OSVDB designation
     * if on exists.
     * 
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return String OSVDB designation
     */
    public function get_osvdb() {
		return htmlspecialchars($this->osvdb);
    }
    
    public function lookup_by_name($name) {
    	$sql = array('select * from vuln where vuln_name = \'?s\'', $name);
        $result = $this->db->fetch_object_array($sql);
        if (isset($result[0])) {
            $this->id = $result[0]->vuln_id;
            $this->name = $result[0]->vuln_name;
            $this->description = $result[0]->vuln_description;
            $this->cve = $result[0]->vuln_cve;
            $this->osvdb = $result[0]->vuln_osvdb;
        }       
    }
    
    public function lookup_by_name_cve($name, $cve) {
        $sql = array('select * from vuln where vuln_name = \'?s\' and vuln_cve = \'?s\'', $name, $cve);
        $result = $this->db->fetch_object_array($sql);
        if (isset($result[0])) {
            $this->id = $result[0]->vuln_id;
            $this->name = $result[0]->vuln_name;
            $this->description = $result[0]->vuln_description;
            $this->cve = $result[0]->vuln_cve;
            $this->osvdb = $result[0]->vuln_osvdb;
        }       
    }
    
    /**
     * Persist the object back to the data layer.
     * 
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Boolean True if the save worked properly, false otherwise.
     */
    public function save() {
    	$sql = '';
    	if ($this->id > 0 ) {
    		// Update an existing vulnerability
	    	$sql = array(
	    		'UPDATE vuln SET ' .
		    		'vuln_name = \'?s\', ' .
		    		'vuln_description = \'?s\', ' .
		    		'vuln_cve = \'?s\', ' .
		    		'vuln_osvdb = \'?s\' ' .
	    		'WHERE vuln_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_description(),
	    		$this->get_cve(),
	    		$this->get_osvdb(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO vuln ' .
					'SET vuln_name = \'?s\', ' .
					'vuln_description = \'?s\', ' .
					'vuln_cve = \'?s\', ' .
					'vuln_osvdb = \'?s\'',
	    		$this->get_name(),
	    		$this->get_description(),
	    		$this->get_cve(),
	    		$this->get_osvdb()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->last_id > 0) {
	    		$this->id = $result[0]->last_id;
	    	}
    	}
    	
    	//Add/update tags if any
    	$sql = array(
    		'DELETE FROM vuln_x_tag WHERE vuln_id = ?i',
    			$this->get_id()
    	);
    	$this->db->iud_sql($sql);
    	if (is_array($this->get_tag_ids()) && count($this->get_tag_ids()) > 0){
    		foreach ($this->get_tag_ids() as $tag_id){
    			$sql = array('INSERT INTO vuln_x_tag SET vuln_id = ?i, tag_id = ?i',
    					$this->get_id(),$tag_id
    			);
    			$this->db->iud_sql($sql);
    		}
    	}
    	return $retval;
    }
    
    /**
     * Set the CVE designation
     * 
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @param String $cve
     */
	public function set_cve($cve) {
    	if ($cve != '')
    		$this->cve = $cve;
    }
    
    /**
     * Set the vulnerability detailed description.
     * 
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @param String $description (Can be '' if we want to get rid of on old desc)
     */
     public function set_description($description='') {
    		$this->description = $description;
    }
    
     /**
     * Set the vulnerability name.
     * 
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @param String $name
     */   
    public function set_name($name) {
    	if ($name != '')
    		$this->name = htmlspecialchars($name);
    }
        
     /**
     * Set the vulnerability OSVDB identifier.
     * 
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @param String $osvdb
     */
     public function set_osvdb($osvdb) {
    	if ($osvdb != '')
    		$this->osvdb = htmlspecialchars($osvdb);
    }
    
    /**
     * Set tags associated with this vulnerability
     *
     * @access public
     * @author Ubani Balogun <ubani@sas.upenn.edu>
     * @param Array an array of tag ids (integers)
     * @return void
     */
    public function set_tag_ids($array){
    	if (is_array($array)){
    		$array = array_map('intval',$array);
    		$this->tag_ids = $array;
    	}
    }
    /**
     * Returns the tag ids associated with vulnerability
     *
     * @access public
     * @author Ubani Balogun <ubani@sas.upenn.edu>
     * @return Array an array of tag ids (integers)
     */
    public function get_tag_ids(){
    	return $this->tag_ids;
    }
    
    /**
     * Adds a tag id to the tag_ids attribute
     *
     * @access public
     * @author Ubani Balogun <ubani@sas.upenn.edu>
     * @param int the id to add
     * @return void
     */
    public function add_tag_id($id){
    	$this->tag_ids[] = intval($id);
    }
    
    /**
     * Gets the names off all tags associated with an vulnerability
     *
     * @access public
     * @author Ubani Balogun <ubani@sas.upenn.edu>
     * @return Array an array of tag names (strings)
     */
    public function get_tag_names(){
    	$retval = array();
    	$tag_ids = $this->get_tag_ids();
    	require_once('class.Tag.php');
    	if (is_array($tag_ids)){
    		foreach ($tag_ids as $tag_id){
    			$tag = new Tag($tag_id);
    			$retval[] = $tag->get_name();
    		}
    	}
    	return $retval;
    }
    
    /**
     * Value function callback for tag values of add alter form
     * 
     * @access public
     * @author Ubani A Balogun
     * @return String tag names delimited by ", "
     */
    public function get_tag_names_callback(){
    	$tag_names = $this->get_tag_names();
    	return implode(", ",$tag_names);
    }
    
    /**
     * Process the tag values received from the add/edit form
     * 
     * @access public 
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return void
     */
    public function process_tag_values($tags){
    	require_once('class.Tag.php');
    	$tag_ids = array();
    	$names = explode(",",$tags);
    	if (is_array($names)){
    		foreach ($names as $each){
    			$tag_name = trim($each);
    			if ($tag_name){
    				$tag = new Tag();
    				$tag->lookup_by_name($tag_name);
    				$tag_ids[] = $tag->get_id();
    			}
    		}
    	}
    	$this->set_tag_ids($tag_ids);
    
    }
    
    /**
     * Gets the object as an array
     * 
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array an associative array of the objects attributes
     */
    public function get_object_as_array(){
    	return array(
    			"id" => $this->get_id(),
    			"name" => $this->get_name(),
    			"description" => $this->get_description(),
    			"cve" => $this->get_cve(),
    			"osvdb" => $this->get_osvdb(),
    	);
    }
    

} /* end of class Vuln */

?>