<?php
/**
 * HECTOR - class.Infoblox_query.php
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

/**
 * Infoblox_query
 *
 * @access public
 * @package HECTOR
 */
class Infoblox_query {


    // --- ATTRIBUTES ---

    /**
     * Unique id reflected from the databasse
     *
     * @access private
     * @var int
     */
    protected $id = null;
    
    /**
     * ip address
     *
     * @var String
     */
    private $ip;
    
    /**
     * query datetime
     * 
     * @var timestamp
     */
    private $datetime;
    
    /**
     * domain name
     * 
     * @var string
     */
    
    private $domain_name;
    
    
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
    				'SELECT * FROM infoblox_query WHERE infoblox_query_id = ?i',
    				$id
    		);
    		$result = $this->db->fetch_object_array($sql);
    		if (isset($result[0])) {
    			$this->id = $result[0]->infoblox_query_id;
    			$this->domain_name = $result[0]->infoblox_query_domain_name;
    			$this->datetime = $result[0]->infoblox_query_datetime;
    			$this->ip = $result[0]->infoblox_query_src_ip;
    		}
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
    		$sql=array('Delete FROM infoblox_query WHERE infoblox_query_id =?i',
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
     * Return the display safe datetime
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return String The datetime
     */
    public function get_datetime() {
    	return htmlspecialchars($this->datetime);
    }
    
    /**
     * Return the display safe domain name
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return String The domain name
     */
    public function get_domain_name() {
    	return htmlspecialchars($this->domain_name);
    }
    
    /**
     * Return the display safe ip address
     *
     * @access public
     * @author Josh Bauer <bauerj@mlhs.org>
     * @return String The ip address
     */
    public function get_ip() {
    	return htmlspecialchars($this->ip);
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
    		// Update an existing infoblox query
    		$sql = array(
    				'UPDATE infoblox_query SET ' .
    				'infoblox_query_domain_name = \'?s\', ' .
    				'infoblox_query_src_ip = \'?s\', ' .
    				'infoblox_query_datetime = \'?s\', ' .
    				'WHERE infoblox_query_id = \'?i\'',
    				$this->get_domain_name(),
    				$this->get_ip(),
    				$this->get_datetime(),
    				$this->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
    				'INSERT INTO infoblox_query ' .
    				'SET infoblox_query_domain_name = \'?s\', ' .
    				'infoblox_query_src_ip = \'?s\', ' .
    				'infoblox_query_datetime = \'?s\'',
    				$this->get_domain_name(),
    				$this->get_ip(),
    				$this->get_datetime()
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
    
    /**
     * Set the infoblox query datetime.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param String $datetime
     */
    public function set_datetime($datetime) {
    	if ($datetime != '')
    		$this->datetime = htmlspecialchars($datetime);
    }
    
    /**
     * Set the infoblox query domain.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param String $domain_name
     */
    public function set_domain_name($domain_name) {
    	if ($domain_name != '')
    		$this->domain_name = htmlspecialchars($domain_name);
    }
    
    /**
     * Set the infoblox query ip address.
     *
     * @author Josh Bauer <bauerj@mlhs.org>
     * @access public
     * @param String $ip
     */
    public function set_ip($ip) {
    	if ($ip != '')
    		$this->ip = htmlspecialchars($ip);
    }
    /*Aug 26 04:02:16*/
    public static function conv_datetime($datetime) {
    	$tmp = DateTime::createFromFormat('M j H:i:s', $datetime);
    	return $tmp->format('Y-m-d H:i:s');
    }
    
} /* end of class Infoblox_query */
?>