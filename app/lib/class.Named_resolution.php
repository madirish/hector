<?php
/**
 * HECTOR - class.Named_resolution.php
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
require_once('class.Domain.php');

/**
 * Named_resolution
 *
 * @access public
 * @package HECTOR
 */
class Named_resolution {


    // --- ATTRIBUTES ---

    /**
     * Unique id reflected from the database
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
     * ip address numeric
     *
     * @var int
     */
    private $ip_numeric;
    
    /**
     * resolution datetime
     * 
     * @var String
     */
    private $datetime;
    
    /**
     * domain
     * 
     * @var Domain
     */
    
    private $domain;
    
    
    /**
     * Generic constructor.  Look up the object
     * from the database or instantiate a blank
     * one.
     *
     * @access public
     * @param  int id
     * @return void
     */
    public function __construct($id = '')
    {
    	$this->db = Db::get_instance();
    	$this->log = Log::get_instance();
    	if ($id != '') {
    		$sql = array(
    				'SELECT * FROM named_resolution WHERE named_resolution_id = ?i',
    				$id
    		);
    		$result = $this->db->fetch_object_array($sql);
    		if (isset($result[0])) {
    			$this->id = $result[0]->named_resolution_id;
    			$this->domain = new Domain($result[0]->domain_id);
    			$this->datetime = $result[0]->named_resolution_datetime;
    			$this->ip = $result[0]->named_resolution_src_ip;
    			$this->ip_numeric = $result[0]->named_resolution_src_ip_numeric;
    		}
    	}
    }
    
    /**
     * Delete the record from the database
     *
     * @access public
     * @return Boolean True if the delete succeeds, False otherwise.
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		$sql=array('Delete FROM named_resolution WHERE named_resolution_id =?i',
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
     * @return int Unique id from the data layer or zero
     */
    public function get_id() {
    	return intval($this->id);
    }
    
    /**
     * Return the display safe datetime
     *
     * @access public
     * @return String The datetime
     */
    public function get_datetime() {
    	return htmlspecialchars($this->datetime);
    }
    
    /**
     * Return the display safe domain name
     *
     * @access public
     * @return Domain the domain
     */
    public function get_domain() {
    	return $this->domain;
    }
    
    /**
     * Return the display safe ip address
     *
     * @access public
     * @return String The ip address
     */
    public function get_ip() {
    	return htmlspecialchars($this->ip);
    }
    
    /**
     * Return the numeric value of the ip address
     *
     * @access public
     * @return  int The ip address numeric value
     */
    public function get_ip_numeric() {
    	return $this->ip_numeric;
    }
    
    
    
    /**
     * Persist the object back to the data layer.
     *
     * @access public
     * @return Boolean True if the save worked properly, false otherwise.
     */
    public function save() {
    	$sql = '';
    	if ($this->id > 0 ) {
    		// Update an existing named resolution
    		$sql = array(
    				'UPDATE named_resolution SET ' .
    				'domain_id = \'?i\', ' .
    				'named_resolution_src_ip = \'?s\', ' .
    				'named_resolution_src_ip_numeric = \'?i\', ' .
    				'named_resolution_datetime = \'?s\' ' .
    				'WHERE named_resolution_id = \'?i\'',
    				$this->get_domain()->get_id(),
    				$this->get_ip(),
    				$this->get_ip_numeric(),
    				$this->get_datetime(),
    				$this->get_id()
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
    				'INSERT INTO named_resolution ' .
    				'SET domain_id = \'?i\', ' .
    				'named_resolution_src_ip = \'?s\', ' .
    				'named_resolution_src_ip_numeric = \'?i\', ' .
    				'named_resolution_datetime = \'?s\'',
    				$this->get_domain()->get_id(),
    				$this->get_ip(),
    				$this->get_ip_numeric(),
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
     * Set the named resolution datetime.
     *
     * @access public
     * @param String $datetime
     */
    public function set_datetime($datetime) {
    	if ($datetime != '')
    		$this->datetime = htmlspecialchars($datetime);
    }
    
    /**
     * Set the named resolution domain.
     *
     * @access public
     * @param Domain $domain
     */
    public function set_domain($domain) {
    	$this->domain = $domain;
    }
    
    /**
     * Set the named resolution domain by id.
     *
     * @access public
     * @param int $domain_id
     */
    public function set_domain_by_id($domain_id) {
    	$this->domain = new Domain($domain_id);
    }
    
    /**
     * Set the named resolution ip address.
     *
     * @access public
     * @param String $ip
     */
    public function set_ip($ip) {
    	if ($ip != '') {
    		$this->ip = htmlspecialchars($ip);
    		$this->ip_numeric = ip2long($ip);
    	}
    }
    
    /**
     * Set the named resolution ip address.
     *
     * @access public
     * @param int $ip_numeric
     */
    public function set_ip_numeric($ip_numeric) {
    	if ($ip_numeric > 0) {
    		$this->ip = htmlspecialchars(long2ip($ip_numeric));
    		$this->ip_numeric = $ip_numeric;
    	}
    }
    
    /*Aug 26 04:02:16*/
    public static function conv_datetime($datetime) {
    	$tmp = DateTime::createFromFormat('M j H:i:s', $datetime);
    	return $tmp->format('Y-m-d H:i:s');
    }
    
} /* end of class Named_resolution */
?>