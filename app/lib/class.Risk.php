<?php
/**
 * HECTOR - class.Risk.php
 *
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
 * Risk ratings are used to sort things like vulnerability details.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Risk extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;

    /**
     * Unique ID from the data layer
     *
     * @access protected
     * @var int Unique id
     */
    protected $id = null;

	/**
	 * Risk name
	 * 
	 * @access private
	 * @var String The name of the risk
	 */
    private $name;

	/**
	 * Risk weight (0 for none, higher for more severe)
	 * 
	 * @access private
	 * @var Int Unsigned int for relative risk rating (higher is worse)
	 */
    private $weight = 0;
    
    /**
     * Instance of the Log
     * 
     * @access private
     * @var Log An instance of the Log
     */
    private $log = null;

	/**
	 * Vulnerability details with this risk
	 * 
	 * @access public
	 * @var Array The vuln_detail_ids for Vuln_detail objects
	 */
    public $vuln_detail_ids = array();
    
    /**
     * Just the most recent instances of a particular vuln on 
     * a specific host
     * 
     * @var Array The vuln_detail_ids for the most recent Vuln_detail objects
     */
    public $most_recent_vuln_detail_ids = array();

    // --- OPERATIONS ---

    /**
     * Construct a new blank Risk or instantiate one
     * from the data layer based on ID
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID of the Risk
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM risk WHERE risk_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
            // if we don't get a valid risk bail out
            if (count($result) < 1) return;
			$this->set_id($result[0]->risk_id);
			$this->set_name($result[0]->risk_name);
			$this->set_weight($result[0]->risk_weight);
			$sql = array(
				'SELECT vuln_detail_id FROM vuln_detail WHERE risk_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
	    	if (is_array($result) && count($result) > 0) {
	    		foreach($result as $row) {
	    			$this->vuln_detail_ids[] = $row->vuln_detail_id;
	    		}
	    	}
			$sql = array(
				'select distinct(d.host_id), 
					max(d.vuln_detail_datetime), 
					d.vuln_detail_id, 
					v.vuln_name, 
					d.host_id 
				  FROM vuln_detail d, vuln v 
				  WHERE d.vuln_id = v.vuln_id and d.risk_id = ?i  
				  GROUP by d.host_id',
				$id
			);
			$resultdist = $this->db->fetch_object_array($sql);
	    	if (is_array($resultdist) && count($resultdist) > 0) {
	    		foreach($resultdist as $row) {
	    			$this->most_recent_vuln_detail_ids[] = $row->vuln_detail_id;
	    		}
	    	}
		}
    }
    
    public function add_vuln_detail_id($id) {
    	$this->vuln_detail_ids[] = intval($id);
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
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM risk WHERE risk_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Delete mappings
	    	$sql = array(
	    		'UPDATE vuln_detail SET risk_id = 0 WHERE risk_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	return $retval;
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a risk.
	 * 
	 * @access public
	 * @return Array The array for the default CRUD template.
	 */
	public function get_add_alter_form() {
		return array (
			array('label'=>'Risk name',
					'type'=>'text',
					'name'=>'riskname',
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'Risk weight',
					'type'=>'text',
					'name'=>'riskweight',
					'value_function'=>'get_weight',
					'process_callback'=>'set_weight')
		);
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT t.risk_id FROM risk t WHERE t.risk_id > 0';
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
			$sql .= ' ORDER BY t.risk_weight DESC';
		}
		return $sql;
	}

	/**
	 * Get the displays for the default details template
	 * 
	 * @return Array Displays for default template
	 */
	public function get_displays() {
		return array('Name'=>'get_linked_name','Weight'=>'get_weight');
	}
	
    /**
     * Get the unique ID for the object
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The unique ID of the object
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
        return 'Risk';
    } 
    
    /**
     * Looks up a Risk by name, creating it if it doesn't exist
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The name of the Risk to search for
     * @return Int the id of the risk. 0 on an error
     */
    public function lookup_by_name($name){
    	$sql = array(
    		'SELECT risk_id FROM risk WHERE LOWER(risk_name) = LOWER(\'?s\')',
    			$name
    	);
    	$result = $this->db->fetch_object_array($sql);
    	// Found an existing Risk
    	if (isset($result[0])){
    		$this->__construct($result[0]->risk_id);
    	}
    	// Create a new Risk
    	else {
    		$this->set_name(strtolower($name));
    		$this->save();
    	}
    	return $this->get_id();
    }
    
    /**
     * The HTML linked title of the Risk
     *  
     *  @access public 
     *  @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     *  @return String the HTML linked safe title of the Risk (links to summary)
     */
    public function get_linked_name(){
    	return "<a href='?action=risk_details&id=$this->id'>" . $this->get_name() . "</a>";
    }

	/**
	 * The HTML safe name of the Risk
	 * 
	 * @access public
	 * @return String The HTML display safe name of the Risk.
	 */
    public function get_name() {
		return htmlspecialchars($this->name);
    }
    
    /**
     * Returns the risk object as an array
     * @access public
     * @return Array an associative array of risk attributes 
     */
    public function get_object_as_array(){
    	return array(
    			'id' => $this->get_id(),
    			'name' => $this->get_name(),
    			'weight' => $this->get_weight()
    	);
    }
    
    public function get_weight() {
    	return htmlspecialchars($this->weight);
    }
    
    /**
     * Returns an array of vulnerability ids mapped to this risk
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Array an array of vuln_detail_ids (int)
     */
    public function get_vuln_detail_ids(){
		return $this->vuln_detail_ids;
    }
    
    public function get_most_recent_vuln_detail_ids(){
    	return $this->most_recent_vuln_detail_ids;
    }
    
	/**
	 * Persist the Risk to the data layer
	 * 
	 * @access public
	 * @return Boolean True if everything worked, FALSE on error.
	 */
    public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing risk
	    	$sql = array(
	    		'UPDATE risk SET risk_name = \'?s\', risk_weight = ?i WHERE risk_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_weight(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO risk SET risk_name = \'?s\', risk_weight = ?i',
    			$this->get_name(),
    			$this->get_weight()
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
     * Set the id attribute.
     * 
     * @access protected
     * @param Int The unique ID from the data layer
     */
    protected function set_id($id) {
    	$this->id = intval($id);
    }

	/**
	 * Set the name of the Risk
	 * 
	 * @access public
	 * @param String The name of the risk
	 */
    public function set_name($name) {
    	$this->name = $name;
    }
    
    /**
     * Set the weight attribute.
     * 
     * @access protected
     * @param Int The weight integer (higher is worse)
     */    
    public function set_weight($weight) {
    	$this->weight = strip_tags($weight);
    }

} /* end of class Risk */

?>