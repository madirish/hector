<?php
/**
 * HECTOR - class.Domain.php
 *
 * @author Josh Bauer <bauerj@mlhs.org>
 *
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
//require_once('class.Service.php');

/**
 * Domain
 *
 * @access public
 * @package HECTOR
 */
class Domain {


    // --- ATTRIBUTES ---

    /**
     * Unique id reflected from the databasse
     *
     * @access private
     * @var int
     */
    protected $id = null;
    
    /**
     * Domain name.
     *
     * @var String
     */
    private $name;
    
    /**
     * Is the domain marked as malicious.
     *
     * @var bool
     */
    private $is_malicious;
    
    /**
     * Date that the domin was last marked malicious
     *
     * @var String
     */
    private $marked_malicious_datetime;
    
    /**
     * Service logged the domain
     *
     * @var Service
     */
    private $service;
    
    
    /**
     * Generic constructor.  Look up the object
     * from the database or instantiate a blank
     * one.
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @param  int id
     * @return void
     */
    public function __construct($id = '')
    {
    	$this->db = Db::get_instance();
    	$this->log = Log::get_instance();
    	if ($id != '') {
    		$sql = array(
    				'SELECT * FROM domain WHERE domain_id = ?i',
    				$id
    		);
    		$result = $this->db->fetch_object_array($sql);
    		if (isset($result[0])) {
    			$this->id = $result[0]->domain_id;
    			$this->name = $result[0]->domain_name;
    			$this->is_malicious = $result[0]->is_malicious > 0;
    			$this->marked_malicious_datetime = $result[0]->marked_malicious_datetime;
    			$this->service = new Service($result[0]->service_id);
    		}
    	} else {
    		$this->service = new Service();
    	}
    }
    
    /**
     * Delete the record from the database
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return Boolean True if the delete succeeds, False otherwise.
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		$sql=array('Delete FROM domain WHERE domain_id =?i',
    				$this->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	$this->set_id(null);
    	return $retval;
    }
    
    /**
     * Return the unique id from the data layer
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return int Unique id from the data layer or zero
     */
    public function get_id() {
    	return intval($this->id);
    }
    
    /**
     * Return the display safe name
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return String The domain name
     */
    public function get_name() {
    	return htmlspecialchars($this->name);
    }
    
    /**
     * Is the domain malicious
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return bool 
     */
    public function get_is_malicious() {
    	return $this->is_malicious;
    }
    
    /**
     * Get display safe datetime when marked malicious
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return bool
     */
    public function get_marked_malicious_datetime() {
    	return htmlspecialchars($this->marked_malicious_datetime);
    }
    
    /**
     * Get Service
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return Service
     */
    public function get_service() {
    	return $this->service;
    }
    
    /**
     * Populate object if a domain name exists in the database
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     */
    public function lookup_by_name($name) {
    	$sql = array('select * from domain where domain_name = \'?s\'', $name);
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0])) {
    		$this->id = $result[0]->domain_id;
    		$this->name = $result[0]->domain_name;
    		$this->is_malicious = $result[0]->is_malicious > 0;
    		$this->marked_malicious_datetime = $result[0]->marked_malicious_datetime;
    		$this->service = new Service($result[0]->service_id);
    	}
    }
    
    /**
     * Persist the object back to the data layer.
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return Boolean True if the save worked properly, false otherwise.
     */
    public function save() {
    	$sql = '';
    	if ($this->id > 0 ) {
    		// Update an existing domain
    		$sql = array(
    				'UPDATE domain SET ' .
    				'domain_name = \'?s\', ' .
    				'domain_is_malicious = \'?i\', ' .
    				'domain_marked_malicious_datetime = \'?s\', ' .
    				'malware_service_id = \'?i\', ' .
    				'WHERE domain_id = \'?i\'',
    				$this->get_name(),
    				$this->get_is_malicious(),
    				$this->get_marked_malicious_datetime(),
    				$this->get_service()->get_id(),
    				$this->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
    				'INSERT INTO domain ' .
    				'SET domain_name = \'?s\', '.
    				'domain_is_malicious = \'?i\', ' .
    				'domain_marked_malicious_datetime = \'?s\', ' .
    				'malware_service_id = \'?i\'',
    				$this->get_name(),
    				$this->get_is_malicious(),
    				$this->get_marked_malicious_datetime(),
    				$this->get_service()->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    		// Now set the id
    		$sql = 'SELECT LAST_INSERT_ID() AS last_id';
    		$result = $this->db->fetch_object_array($sql);
    		if (isset($result[0]) && $result[0]->last_id > 0) {
    			$this->id = $result[0]->last_id;
    		}
    	}
    	 
    	return $retval;
    }
    
    /**
     * Set the object's unique id
     *
     * @access protected
     * @param  int The unique id for the object
     * @return void
     */
	protected function set_id($id) {
       $this->id = (int) $id;
    }
    
    public function set_name($name) {
    	if ($name != '') {
    		$this->name = htmlspecialchars($name);
    	}
    }
    
    /**
     * Set is_malicious.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param String $is_malicious
     */
    public function set_is_malicious($is_malicious) {
    	$this->is_malicious = $is_malicious > 0;
    }
    
    /**
     * Set marked malicious datetime.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param String $datetime
     */
    public function set_marked_malicious_datetime($datetime) {
    	if ($datetime != '') {
    		$this->marked_malicious_datetime = htmlspecialchars($datetime);
    	}
    }
    
    /**
     * Set Service by id.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param int $service_id
     */
    public function set_service_by_id($service_id) {
    	$this->service = new Service($service_id);
    }
    /**
     * Set Service.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param Service $service
     */
    public function set_service($service) {
    	$this->service = $service;
    }
    
} /* end of class Domain */
?>