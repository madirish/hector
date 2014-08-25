<?php
/**
 * HECTOR - class.Darknet.php
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
 * Darknets are free taxonomies used to group hosts.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Darknet extends Maleable_Object {


    // --- ATTRIBUTES ---

    /**
     * The two letter country code of the source_ip
     * 
     * @access private
     * @var String The two letter country code for the source
     */
    private $country_code;
    
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;

    /**
     * The destination IP address (i.e. the darknet sensor)
     * 
     * @access private
     * @var Int The long integer IP address
     */
    private $dst_ip;

    /**
     * The destination port (i.e. on the darknet sensor)
     * 
     * @access private
     * @var Int The long integer IP address
     */
    private $dst_port;

    /**
     * Unique ID from the data layer
     *
     * @access protected
     * @var int Unique id
     */
    protected $id = null;
    
    /**
     * Instance of the Log
     * 
     * @access private
     * @var Log An instance of the Log
     */
    private $log = null;

    /**
     * The IP protocol (tcp/udp)
     * 
     * @access private
     * @var String The IP protocol of the probe
     */
    private $proto;

    /**
     * The timestamp of the port probe
     * 
     * @access public
     * @var Timestamp The timestamp of the port probe
     */
    private $received_at;

    /**
     * The source IP address of the probe
     * 
     * @access private
     * @var Int The long integer IP address
     */
    private $src_ip;

    /**
     * The source port of the probe
     * 
     * @access private
     * @var Int The long integer IP address
     */
    private $src_port;

    // --- OPERATIONS ---

    /**
     * Construct a new blank Darknet or instantiate one
     * from the data layer based on ID
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID of the Darknet
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
        $this->log = Log::get_instance();
        if ($id != '') {
            $sql = array(
                'SELECT id AS darknet_id, src_ip, dst_ip, src_port, dst_port, proto, received_at, country_code FROM darknet WHERE id = ?i',
                $id
            );
            $result = $this->db->fetch_object_array($sql);
            if (is_object($result[0])) {
                $this->set_country_code($result[0]->country_code);
                $this->set_id($result[0]->darknet_id);
                $this->set_dst_ip($result[0]->dst_ip);
                $this->set_dst_port($result[0]->dst_port);
                $this->set_proto($result[0]->proto);
                $this->set_received_at($result[0]->received_at);
                $this->set_src_ip($result[0]->src_ip);
                $this->set_src_port($result[0]->src_port);	
            }
        }
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
                'DELETE FROM darknet WHERE id = \'?i\'',
                $this->get_id()
            );
            $retval = $this->db->iud_sql($sql);
        }
        return $retval;
    }

    /**
     *  This function directly supports the Collection class.
     *
     * @return String SQL select string
     */
    public function get_collection_definition($filter = '', $orderby = '') {
        $sql = 'SELECT d.id as darknet_id ' .
                'FROM darknet d ' .
                'WHERE d.dst_port >= 0 ' .
                'AND d.id > 0';
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
            $sql .= ' ORDER BY d.received_at desc';
        }
        return $sql;
    }
    
    /**
     * Get a collection of darknets by country
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The two letter country code
     * @param String The SQL order by clause
     * @return String The SQL to pass to the Collection class
     */
    public function get_collection_by_country($country, $orderby) {
        $country = substr($country, 0, 2);
    	$filter = ' AND d.country_code = \'' . mysql_real_escape_string($country) . '\' ';
        return $this->get_collection_definition($filter, $orderby);
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
        return 'Darknet';
    } 

    /**
     * The source IP of the probe
     * 
     * @access public
     * @return Int The source IP of the probe
     */
    public function get_src_ip() {
        return intval($this->src_ip);
    }
    
    /**
     * The country code of the probe
     * 
     * @access public
     * @return String The country code of the source IP
     */
    public function get_country_code() {
        return htmlspecialchars($this->country_code);
    }
    
    /**
     * The destination IP of the probe
     * 
     * @access public
     * @return Int The destination IP of the probe
     */
    public function get_dst_ip() {
        return intval($this->dst_ip);
    }
    
    /**
     * The IP protocol of the probe
     * 
     * @access public
     * @return String The IP protocol of the probe
     */
    public function get_proto() {
        $retval = false;
        switch ($this->proto) {
        	case 'tcp': 
                $retval = 'tcp';
                break;
            case 'udp':
                $retval = 'udp';
                break;
            case 'icpm':
                $retval = 'icmp';
                break;
            default:
                $retval = false;
        }
        return $retval;
    }
    
    /**
     * The timestamp of the probe
     * 
     * @access public
     * @return Timestamp The timestamp of the probe
     */
    public function get_received_at() {
        return $this->received_at;
    }
    
    /**
     * The destination port of the probe
     * 
     * @access public
     * @return Int The destination port of the probe
     */
    public function get_dst_port() {
        return intval($this->dst_port);
    }
    
    /**
     * The source port of the probe
     * 
     * @access public
     * @return Int The source port of the probe
     */
    public function get_src_port() {
        return intval($this->src_port);
    }

    /**
     * Persist the Darknet to the data layer
     * 
     * @access public
     * @return Boolean True if everything worked, FALSE on error.
     */
    public function save() {
        $retval = FALSE;
        if ($this->id > 0 ) {
            // Update an existing tag
            $sql = array(
                'UPDATE darknet SET src_ip = \'?i\', ' .
                'dst_ip = \'?i\', ' .
                'src_port = \'?i\', ' .
                'dst_port = \'?i\', ' .
                'proto = \'?s\', ' .
                'received_at = \'?i\' WHERE id = \'?i\'',
                $this->get_src_ip(),
                $this->get_dst_ip(),
                $this->get_src_port(),
                $this->get_dst_port(),
                $this->get_proto(),
                $this->get_received_at(),
                $this->get_id()
            );
            $retval = $this->db->iud_sql($sql);
        }
        else {
            $sql = array(
                'INSERT INTO darknet SET src_ip = \'?i\', ' .
                'dst_ip = \'?i\', ' .
                'src_port = \'?i\', ' .
                'dst_port = \'?i\', ' .
                'proto = \'?s\', ' .
                'received_at = \'?i\'',
                $this->get_src_ip(),
                $this->get_dst_ip(),
                $this->get_src_port(),
                $this->get_dst_port(),
                $this->get_proto(),
                $this->get_received_at()
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
     * Set the country code attribute.
     * 
     * @access public
     * @param String The two letter country code.
     */
    public function set_country_code($code) {
        $code = substr(strtoupper($code),0,2);
        $code = preg_replace('/[^A-Z]/', '', $code);
        $this->country_code = $code;
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
     * Set the source IP of the probe
     * 
     * @access public
     * @param Int The source IP of the probe
     */
    public function set_src_ip($ip) {
        $this->src_ip = intval($ip);
    }
    
    /**
     * Set the destination IP of the probe
     * 
     * @access public
     * @param Int The destination IP of the probe
     */
    public function set_dst_ip($ip) {
        $this->dst_ip = intval($ip);
    }
    
    /**
     * Set the IP protocol of the probe
     * 
     * @access public
     * @param String The IP protocol of the probe
     */
    public function set_proto($proto) {
        switch ($proto) {
            case 'tcp': 
                $this->proto = 'tcp';
                break;
            case 'udp':
                $this->proto = 'udp';
                break;
            case 'icpm':
                $this->proto = 'icmp';
                break;
            default:
                $this->proto = '';
        }
    }
    
    /**
     * Set the timestamp of the probe
     * 
     * @access public
     * @return Datetime The timestamp of the probe
     */
    public function set_received_at($datetime) {
        $this->received_at = date("Y-m-d H:i:s", strtotime($datetime));
    }
    
    /**
     * Set the destination port of the probe
     * 
     * @access public
     * @param Int The destination port of the probe
     */
    public function set_dst_port($port) {
        $this->dst_port = intval($port);
    }
    
    /**
     * Set the source port of the probe
     * 
     * @access public
     * @param Int The source port of the probe
     */
    public function set_src_port($port) {
        $this->src_port = intval($port);
    }
    
    /**
     * Returns the frequencies of entires for a field in the data layer
     *
     * @param String $field The field from the data layer to count
     * @param string $bound The bound for the data
     * @return Array The frequenies of entries for the field
     */
    public function get_field_frequencies($field,$bound=''){
    	$retval = array();
    	$sql = array('SELECT ?s , count(?s) as frequency FROM darknet d'
    			. ' WHERE id > 0 ' . $bound . ' GROUP BY ?s order by frequency desc', $field, $field, $field);
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0])){
    		foreach ($result as $row){
    			$retval[$row->$field] = $row->frequency;
    		}
    	}
    	return $retval;
    }

} /* end of class Darknet */

?>