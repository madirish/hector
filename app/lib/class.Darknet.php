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
                $this->set_id(intval($result[0]->darknet_id));
                $this->set_dst_ip(intval($result[0]->dst_ip));
                $this->set_dst_port(intval($result[0]->dst_port));
                $this->set_proto($result[0]->proto);
                $this->set_received_at($result[0]->received_at);
                $this->set_src_ip(intval($result[0]->src_ip));
                $this->set_src_port(intval($result[0]->src_port));	
            }
        }
    }
    
    /**
     * May 15 04:19:58 servername kernel: iptables IN=eth0 OUT= MAC=00:1a:4b:dc:c3:68:88:43:e1:2f:45:1b:08:00 SRC=104.193.252.230 DST=208.88.12.61 LEN=40 TOS=0x00 PREC=0x00 TTL=241 ID=54321 PROTO=TCP SPT=46797 DPT=21320 WINDOW=65535 RES=0x00 SYN URGP=0 
     * 
     * @param unknown $log
     */
    public function construct_by_syslog_string($log) {
    	// Split the date off the log
    	$first_colon = strpos($log, ":");
    	$second_colon = strpos($log, ":", $first_colon);
    	$end_of_date = strpos($log, " ", $second_colon);
    	$date = substr($log, 0, $end_of_date);
    	$this->set_received_at($date);
    	$start_of_iptable = strpos($log, "iptables ") +9;
    	$iptable = substr($log, $start_of_iptable);
    	$iptablesarr = explode(" ", $iptable);
    	foreach ($iptablesarr as $piece) {
    		$pieces = explode("=", $piece);
    		if (count($pieces) > 1) {
    			switch ($pieces[0]) {
    				case "SRC":
    					$ip = $pieces[1];
    					if (filter_var($ip, FILTER_VALIDATE_IP)) {
    						$ip = ip2long($ip);
    						$this->set_src_ip($ip);
    					}
    					break;
    				case "DST":
    					$ip = $pieces[1];
    					if (filter_var($ip, FILTER_VALIDATE_IP)) {
    						$ip = ip2long($ip);
    						$this->set_dst_ip($ip);
    					}
    					break;
    				case "SPT":
    					$this->set_src_port(intval($pieces[1])); 
    					break;
    				case "DPT":
    					$this->set_dst_port(intval($pieces[1]));
    					break;
    				case "PROTO":
    					$this->set_proto(strtolower($pieces[1]));
    					break;
    			}
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
                'WHERE d.dst_port > 0 ' .
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
    public function get_collection_by_country($country, $orderby='') {
        $country = substr($country, 0, 2);
    	$filter = ' AND d.country_code = \'' . mysql_real_escape_string($country) . '\' ';
        return $this->get_collection_definition($filter, $orderby);
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
    	return $this->dst_ip;
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
     * Returns the frequencies of entries for a field in the data layer
     *
     * @param String $field The field from the data layer to count
     * @param string $bound The bound for the data
     * @return Array The frequenies of entries for the field
     */
    public function get_field_frequencies($field,$bound=''){
    	$retval = array();
    	$sql = 'SELECT ?s, count(?s) as frequency FROM darknet WHERE id > 0 ';
    	if ($bound != ''){
    		$sql .= ' AND received_at > DATE_SUB(NOW(), INTERVAL ?i DAY)';
    		$sql .= ' GROUP BY ?s ORDER BY frequency DESC';
    		$result = $this->db->fetch_object_array(array($sql,$field,$field,intval($bound),$field));
    	}else{
    		$sql .= ' GROUP BY ?s order by frequency desc';
    		$result = $this->db->fetch_object_array(array($sql,$field,$field,$field));
    	}
    	if (isset($result[0])){
    		foreach ($result as $row){
    			$retval[$row->$field] = $row->frequency;
    		}
    	}
    	return $retval;
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
     * The source IP of the probe
     * 
     * @access public
     * @return Int The source IP of the probe
     */
    public function get_src_ip() {
        return $this->src_ip;
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
     *  Get the percentage value of the top field's frequency
     *
     *  @access public
     *  @return array an associative array with the top field name and percentage
     */
    public function get_top_field_percent($field,$bound=''){
    	$retval = array();
    	if ($field !=''){
    		$field_frequencies = $this->get_field_frequencies($field,$bound);
    		if (!empty($field_frequencies)){
    			$maxs = array_keys($field_frequencies, max($field_frequencies));
    			$top_field = $maxs[0];
    			$top_val = $field_frequencies[$top_field];
    			$total = array_sum($field_frequencies);
    			$percent = round(($top_val / $total) * 100);
    			$retval[$top_field] = $percent;
    		}
    	}
    	return $retval;
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
            	'country_code = (SELECT g.country_code FROM geoip g WHERE g.start_ip_long < \'?i\' AND g.end_ip_long > \'?i\')',
                'received_at = \'?d\' WHERE id = \'?i\'',
                $this->get_src_ip(),
                $this->get_dst_ip(),
                $this->get_src_port(),
                $this->get_dst_port(),
                $this->get_proto(),
                $this->get_src_ip(),
                $this->get_src_ip(),
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
                'received_at = \'?d\', ' . 
            	'country_code = (SELECT g.country_code FROM geoip g WHERE g.start_ip_long < \'?i\' AND g.end_ip_long > \'?i\')',
                $this->get_src_ip(),
                $this->get_dst_ip(),
                $this->get_src_port(),
                $this->get_dst_port(),
                $this->get_proto(),
                $this->get_received_at(),
            	$this->get_src_ip(),
            	$this->get_src_ip()
            );
            $retval = $this->db->iud_sql($sql);
            // Now set the id
            $sql = 'SELECT LAST_INSERT_ID() AS last_id';
            $result = $this->db->fetch_object_array($sql);
            if (isset($result[0]) && $result[0]->last_id > 0) {
            	$this->__construct($result[0]->last_id);
                //$this->set_id(intval($result[0]->last_id));
            }
            else {
            	$this->log->write_error("There was a problem getting the last insert id at Darknet::save()");
            }
            // Update totals
            $tmpDate = new DateTime($this->get_received_at());
            $sql = array('INSERT INTO darknet_totals SET countrytime = \'?s\',
            						country_code = \'?s\',
            						day_of_total = \'?s\',
            						count = 1
            				ON DUPLICATE KEY UPDATE count = count + 1',
            	$tmpDate->format('Y-m-d') . $this->country_code,
            	$this->country_code,
            	$tmpDate->format('Y-m-d')
            );
            $retval = $this->db->iud_sql($sql);
        }
        // Finally set the country code
		$sql = array(
			'SELECT country_code from darknet where id = ?i',
			$this->get_id()
		);
		$result = $this->db->fetch_object_array($sql);
		if (isset($result[0])) {
			$this->set_country_code($result[0]->country_code);
		}
		else {
			$this->log->write_error("There was a problem getting the country_code at Darknet::save()");
   		}
        if (! $retval) {
        	$this->log->write_error("There was a problem saving in Darknet::save()");
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
    	$retval = true;
    	$original_code = $code;
        $code = substr(strtoupper($code),0,2);
        $code = preg_replace('/[^A-Z]/', '', $code);
        if (! $original_code == $code ) {
        	$this->log->write_error("Country code reformatted at Darknet::set_country_code()");
       		$retval = false;
        }
        $this->country_code = $code;
        return $retval;
    }
    
    /**
     * Set the id attribute.
     * 
     * @access protected
     * @param Int The unique ID from the data layer
     */
    protected function set_id($id) {
    	if (! is_int($id)) {
    		$this->log->write_error("Non integer submitted to Darknet::set_id()");
    		return false;
    	}
        $this->id = intval($id);
        return true;
    }

    
    /**
     * Set the destination IP of the probe
     * 
     * @access public
     * @param Long The destination IP of the probe
     */
    public function set_dst_ip($ip) {
    	if (! is_long($ip)) {
    		$this->log->write_error("Invalid IP param datatype at Darknet::set_dst_ip()");
    		return false;
    	}
    	// This validation doesn't seem very reliable, won't validate reserved IP's
    	/* if (filter_var(long2ip($ip), FILTER_VALIDATE_IP)) {
    		$this->log->write_error("IP failed to validate at Darknet::set_dst_ip()");
    		return false;
    	} */
        $this->dst_ip = $ip;
        return true;
    }
    
    /**
     * Set the destination port of the probe
     *
     * @access public
     * @param Int The destination port of the probe
     */
    public function set_dst_port($port) {
    	if (! is_int($port)) {
    		$this->log->write_error("Non-integer submitted to Darknet::set_dst_port()");
    		return false;
    	}
        $this->dst_port = intval($port);
        return true;
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
                $this->log->write_error("Unrecognized protocol [$proto] submitted to Darkenet::set_proto()");
                return false;
        }
        return true;
    }
    
    /**
     * Set the timestamp of the probe
     * 
     * @access public
     * @return Datetime The timestamp of the probe
     */
    public function set_received_at($datetime) {
        $this->received_at = date("Y-m-d H:i:s", strtotime($datetime));
        return true;
    }
    
    
    /**
     * Set the source IP of the probe
     *
     * @access public
     * @param Int The source IP of the probe
     */
    public function set_src_ip($ip) {
    	if (! is_int($ip)) {
    		$this->log->write_error("Non-integer IP param datatype at Darknet::set_src_ip() - got " . gettype($ip));
    		return false;
    	}
    	// This validation doesn't seem very reliable, won't validate reserved IP's
    	/* if (filter_var(long2ip($ip), FILTER_VALIDATE_IP)) {
    		$this->log->write_error("IP failed to validate at Darknet::set_src_ip() - got " . long2ip($ip));
    		return false;
    	} */
    	$this->src_ip = $ip;
    	return true;
    }
    
    /**
     * Set the source port of the probe
     * 
     * @access public
     * @param Long The source port of the probe
     */
    public function set_src_port($port) {
    	if (! is_long($port)) {
    		$this->log->write_error("Non-integer submitted to Darknet::set_src_port()");
    		return false;
    	}
        $this->src_port = $port;
        return true;
    }
    

} /* end of class Darknet */

?>