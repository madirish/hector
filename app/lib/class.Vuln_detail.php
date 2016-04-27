<?php
/**
 * HECTOR - class.Vuln_detail.php
 *
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @package HECTOR
 * @todo Filter access based on Support Group
 */
 
/**
 * Error reporting
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Collection.php');
require_once('class.Db.php');
require_once('class.Host.php');
require_once('class.Log.php');
require_once('class.Maleable_Object.php');
require_once('class.Risk.php');
require_once('class.User.php');
require_once('class.Vuln.php');
require_once('interface.Maleable_Object_Interface.php');

/**
 * Occurances of Vulnerabilities.
 *
 * @access public
 * @package HECTOR
 * @author Josh Bauer <joshbauer3@gmail.com>
 */
class Vuln_detail extends Maleable_Object implements Maleable_Object_Interface {
    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;
    
    /**
     * Instance of the Log
     * 
     * @access private
     * @var Log An instance of the Log
     */
    private $log = null;
    
    /**
     * The unique ID from the database.
     *
     * @access private
     * @var Int The unique ID from the data layer.
     */
    protected $id = null;

	/**
	 * Text of the vulnerability detail, usually indicating 
	 * the specific circumstances of the vulnerability or 
	 * any additional details useful for remediation.
	 * 
	 * @access private
	 * @var String The text of the vulnerability detail including specifics re: manifestation, exploitability, etc.
	 */
	private $text;
	
	/**
	 * The timestamp of this report
	 * 
	 * @access private
	 * @var Datetime The date and time that this report was generated (when the vuln was observed)
	 */
	private $datetime = null;
    
    /**
	 * Boolean indicating that the vulnearbility should be 
	 * ignored (usually in case of mitigations).
	 * 
	 * @access private
	 * @var Boolean False if the report has been marked to ignore.
	 */
	private $ignore;
	
	/**
	 * The timestamp of when the vulnerability was marked to 
	 * be ignored.
	 * 
	 * @access private
	 * @var Datetime The time the report was marked to ignore.
	 */
	private $ignore_datetime;
	
	/**
	 * The unique ID, for creating a User object, of the user
	 * who makred the vulnerability for being ignored.
	 * 
	 * @access private
	 * @var Int The id of the User who marked this record ignored.
	 */
	private $ignore_user_id;
	
	/**
	 * Boolean indicating whether or not the vulnerability has
	 * been fixed.  Default is to 0 (or false).
	 * 
	 * @access private
	 * @var Boolean True if this vulnerability has been addressed.
	 */
	private $fixed;
	
	/**
	 * The datetime when the vulnerability was marked as 
	 * fixed.
	 * 
	 * @access private
	 * @var Datetime The time when the vulnerability was remediated.
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
	 * @var Int The User id for who marked fixed
	 */
	private $fixed_user_id;

    /**
     * Instance of the corresponding Vuln object
     *
     * @access private
     * @var Vuln The Vuln object that corresponds to this detail report.
     */
    private $vuln = null;
    
    /**
     * The unique ID of the assocaited Vulnerability, which
     * can be used to instantiate a Vuln object.
     * 
     * @access private
     * @var Int The unique Vuln id for the associated Vuln object.
     */
    private $vuln_id = 0;
    
    /**
     * The ticket (if any) associated with this vulnerability. 
     * This could be a ticket number but is designed to be a
     * URL to ticket details in a ticketing or trackign
     * system (external to HECTOR)
     *
     * @access private
     * @var String External ticket details URL.
     */
    private $ticket = null;
    
    /**
     * The unique ID of the associated Host so that we can 
     * instantiate a Host object to get more details.
     *
     * @access private
     * @var Int Unique Host id that the report refers to (where the vulnerability manifests)
     */
    private $host_id = null;
    
    /**
     * The risk id of the associated Risk for rating
     * 
     * @access private
     * @var Int The database id of the associated risk
     */
    private $risk_id = 0;
    
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
     * @param  Int The optional unique id for the Vuln_detail
     * @return void
     */
    public function __construct($id = '') {
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
				$this->set_id($r->vuln_detail_id);
				$this->set_text($r->vuln_detail_text);
				$this->set_datetime($r->vuln_detail_datetime);
				$this->set_ignore($r->vuln_detail_ignore);
				$this->set_ignore_datetime($r->vuln_detail_ignore_datetime);
				$this->set_ignore_user_id($r->vuln_detail_ignoredby_user_id);
				$this->set_fixed($r->vuln_detail_fixed);
				$this->set_fixed_datetime($r->vuln_detail_fixed_datetime);
				$this->set_fixed_notes($r->vuln_detail_fixed_notes);
				$this->set_fixed_user_id($r->vuln_detail_fixedby_user_id);
				$this->set_vuln_id($r->vuln_id);
				$this->set_risk_id($r->risk_id);
				$this->set_host_id($r->host_id);
				$this->set_ticket($r->vuln_detail_ticket);
			}
		}
    }

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Josh Bauer <joshbauer3@gmail.com>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		$sql=array('DELETE FROM vuln_detail WHERE vuln_detail_id =?i',
    			$this->id
    		);
    		$retval = $this->db->iud_sql($sql);
    	}
    	return $retval;
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
		$vulns = array();
		$collection = new Collection('Vuln');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$vulns[$element->get_id()]=$element->get_name();
			}
		}
		$risks = array();
		$collection = new Collection('Risk');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$risks[$element->get_id()]=$element->get_name();
			}
		}

		return array (
			array('label'=>'Description',
					'type'=>'textarea',
					'name'=>'name',
					'value_function'=>'get_text',
					'process_callback'=>'set_text'),
			array('label'=>'Host IP',
					'type'=>'text',
					'name'=>'description',
					'value_function'=>'get_host_ip',
					'process_callback'=>'set_host_by_ip'),
			array('label'=>'Observed on date',
					'type'=>'date',
					'name'=>'date',
					'value_function'=>'get_datetime',
					'process_callback'=>'set_datetime'),
			array('label'=>'Vulnerability',
					'name'=>'vulnerability[]',
					'type'=>'select',
					'options'=>$vulns,
					'value_function'=>'get_vuln_id',
					'process_callback'=>'set_vuln_id'),
			array('label'=>'Risk',
					'name'=>'risk[]',
					'type'=>'select',
					'options'=>$risks,
					'value_function'=>'get_risk_id',
					'process_callback'=>'set_risk_id'),
			array('label'=>'Ticket URL',
					'type'=>'text',
					'name'=>'ticket',
					'process_callback' =>'set_ticket',
					'value_function' => 'get_ticket')
		);
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @access public
	 * @return String SQL select string
	 * @param String SQL where clause extras, defaults to ''
	 * @param String SQL order by clause, defaults to ''
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
     * The time the vuln report was generated
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Datetime The time when this report was generated, when the vuln was observed.
     */
    public function get_datetime() {
    	if (strtotime($this->datetime)) {
    		return $this->datetime;
    	}
		else {
			return null;
		}
    }
	
	/**
	 * Displays for the template.
	 * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Array Array of details for this record for generic template.
	 */
	public function get_displays() {
		return array('Text'=>'get_text_short',
					'Vulnerability' => 'get_vuln_name',
					'Fixed' => 'get_fixed',
					'Fixed on:' => 'get_fixed_datetime',
					'Fixed by:' => 'get_fixed_user_name',
					'Notes on fix:' => 'get_fixed_notes',
					'Ignored on:' => 'get_ignore_datetime',
					'Ignored by:' => 'get_ignored_user_name',
					'Ticket:' => 'get_ticket',
			);
	}
    
    /**
     * Return whether or not this vulnerability is fixed
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Boolean False if this vulnerability isn't fixed, True otherwise
     */
    public function get_fixed() {
		return (bool) $this->fixed;
    }
    
    /**
     * The time the vuln was addressed
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Datetime The time when this report was addressed, or null.
     */
    public function get_fixed_datetime() {
    	if (strtotime($this->fixed_datetime)) {
    		return $this->fixed_datetime;
    	}
		else {
			return null;
		}
    }
    
    /**
     * Notes associated with the fix
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return String The HTML display safe notes for any fix that was completed.
     */
    public function get_fixed_notes() {
       return htmlspecialchars($this->fixed_notes);
    }
    
    /**
     * The User who marked this vuln report fixed.
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Int The user who reported this vuln fixed.
     */
    public function get_fixed_user_id() {
    	return intval($this->fixed_user_id);
    }
    
    /**
     * The name of the user who marked this detail as
     * fixed, for display.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The name of the user who marked this fixed.
     */
    public function get_fixed_user_name() {
    	$user = new User($this->fixed_user_id);
    	return $user->get_name();
    }
    
    public function get_host_ip() {
    	$host = new Host($this->host_id);
    	return $host->get_ip();
    }
    
    /**
     * Get the associated Host id
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Int The unique Host id for the Host to which this report refers.
     */
    public function get_host_id() {
		return intval($this->host_id);
    }
    
     /**
     * Get the associated Host name
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return String The Host name for the Host to which this report refers.
     */
    public function get_host_name() {
    	$retval = '';
    	if ($this->get_host_id()>0) {
    		$sql = array(
    			'SELECT host_name FROM host WHERE host_id=?i',
    			$this->get_host_id()
    		);
    		$result = $this->db->fetch_object_array($sql);
			if (isset($result[0])) {
				$r = $result[0];
				$retval = $r->host_name;
			}
    	}
		return htmlspecialchars($retval);
    }
    
    public function get_id() {
    	return intval($this->id);
    }
    
    /**
     * Should this report be ignored for reporting purposes?
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Boolean True if Vuln_detail should be ignored for reporting purposes.
     */
    public function get_ignore() {
		return (bool) $this->ignore;
    }
    
    /**
     * The time the vuln was addressed
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @return Datetime The time when this report was ignored, or null.
     */
    public function get_ignore_datetime() {
    	if (strtotime($this->ignore_datetime)) {
    		return $this->ignore_datetime;
    	}
		else {
			return null;
		}
    }
    
    /**
     * The unique ID of the User who marked the record ignored.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The id of the User who marked this record ignored.
     */
    public function get_ignored_user_id() {
    	return intval($this->ignore_user_id);
    }
    
    /**
     * The name of the user who marked this detail as
     * ignored, for display.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The name of the user who marked this ignored.
     */
    public function get_ignored_user_name() {
    	$user = new User($this->ignore_user_id);
    	return $user->get_name();
    }
    
    /**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Vulnerability Detail';
    } 
    
    /**
     * Get the ticket URL associated with 
     * this vulnerability detail.
     * 
     * @access public
     * @author Justin C. Klein Keane
     * @return String The URL to any ticket associated with this report.
     */
    public function get_ticket() {
    	if (filter_var($this->ticket, FILTER_VALIDATE_URL)) {
			return $this->ticket;
    	}	
    	else return null;
    }
    
    public function get_risk_id() {
    	return intval($this->risk_id);
    }
    
    /**
     * The text description of this vulnerability detail.
     * 
     * @access public
     * @author Justin C. Klein Keane
     * @return String The HTML display safe description for this vulnerability detail.
     */
    public function get_text() {
        global $approot;
        require_once($approot . 'software/htmlpurifier/library/HTMLPurifier.auto.php');
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Attr.EnableID', true);
        $purifier = new HTMLPurifier($config);
		return $purifier->purify($this->text);
    }
    
    public function get_text_short($limit=100) {
    	return substr($this->get_text(), 0, $limit);
    }
    /**
     * The associated Vuln object for this detail record.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Vuln The Vuln object associated with this record.
     */
    public function get_vuln() {
    	if ($this->vuln == NULL) {
    		$this->vuln = new Vuln($this->vuln_id);
    	}
    	return $this->vuln;
    }
    
    /**
     * Get vulnerability details for a specific host.
     * 
     * @access public
     * @author Justin C. Klein Keane
     * @param Int The host id for the desired host
     * @param Object An instance of the database connection
     * @return Array An object array with attributes vuln_name, vuln_detail_id, vuln_detail_text, vuln_detail_datetime, vuln_detail_ignore, and vuln_detail_fixed
     */
    public function get_vuln_details_by_host($host_id, $db='') {
        if ($db == '') $db = $this->db;
        $host_id = intval($host_id);
    	$sql = array('SELECT distinct(v.vuln_name), ' .
                    'd.vuln_detail_id, ' .
                    'd.vuln_detail_text, ' .
                    'd.vuln_detail_ignore, ' .
                    'd.vuln_detail_fixed, ' .
                    'r.risk_name ' .
                'FROM vuln_detail d, ' .
                    'vuln v, ' .
                    'risk r ' .
                'WHERE d.vuln_id = v.vuln_id AND ' .
                    'd.host_id = ?i ' .
                    'AND r.risk_id = d.risk_id '. 
                'GROUP BY v.vuln_name ' . 
                'ORDER BY r.risk_weight DESC', 
                $host_id);
        return $db->fetch_object_array($sql);
    }
    
    /**
     * The unique id fo the Vuln object for this detail record.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The unique id of the Vuln object associated with this record.
     */
    public function get_vuln_id() {
    	return (int) $this->vuln_id;
    }
    
    /**
     * Get the name of the Vulnerability associated with
     * this record, for display.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String The name of the Vuln object associated with this record.
     */
    public function get_vuln_name() {
    	$vuln = $this->get_vuln();
    	return $vuln->get_name();
    }
    
    public function lookup_by_vuln_id_host_id_date($vuln_id,$host_id,$date) {
    	$sql = array('SELECT * FROM vuln_detail ' .
                'WHERE vuln_id = ?i ' .
                'AND host_id = ?i ' .
                'AND vuln_detail_datetime = \'?d\'',
                $vuln_id,  
                $host_id,
                $date);
        $result = $this->db->fetch_object_array($sql);
        if (is_array($result) && count($result) > 0 && is_object($result[0])) {           
            $this->__construct($result[0]->vuln_detail_id);
        }
    }
    /**
     * Persist the object back to the database.
     * 
     * @access public
     * @author Justin C. Klein Keane
     * @return Boolean False if something goes awry
     */
    public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing vuln_detail
	    	$sql = array(
	    		'UPDATE vuln_detail SET ' .
	    			'vuln_detail_text = \'?s\', ' .
	    			'vuln_detail_datetime = \'?d\', ' .
	    			'vuln_detail_ignore = ?b, ' .
	    			'vuln_detail_ignore_datetime = \'?d\', ' .
	    			'vuln_detail_ignoredby_user_id = ?i, ' .
	    			'vuln_detail_fixed = ?b, ' .
	    			'vuln_detail_fixedby_user_id = ?i, ' .
	    			'vuln_detail_fixed_datetime = \'?s\', ' .
	    			'vuln_detail_fixed_notes =\'?s\',' .
	    			'vuln_detail_ticket =\'?s\',' .
	    			'risk_id = ?i, ' .
	    			'host_id = ?i, ' .
	    			'vuln_id = ?i ' .
	    		'WHERE vuln_detail_id = ?i',
				$this->get_text(),
				$this->get_datetime(),
	    		$this->get_ignore(),
	    		$this->get_ignore_datetime(),
	    		$this->get_ignored_user_id(),
	    		$this->get_fixed(),
	    		$this->get_fixed_user_id(),
	    		$this->get_fixed_datetime(),
	    		$this->get_fixed_notes(),
	    		$this->get_ticket(),
	    		$this->get_risk_id(),
	    		$this->get_host_id(),
	    		$this->get_vuln_id(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		// Insert a new record
    		// Update an existing vuln_detail
	    	$sql = array(
	    		'INSERT INTO vuln_detail SET ' .
	    			'vuln_detail_text = \'?s\', ' .
	    			'vuln_detail_datetime = \'?d\', ' .
	    			'vuln_detail_ignore = ?b, ' .
	    			'vuln_detail_ignore_datetime = \'?d\', ' .
	    			'vuln_detail_ignoredby_user_id = ?i, ' .
	    			'vuln_detail_fixed = ?b, ' .
	    			'vuln_detail_fixedby_user_id = ?i, ' .
	    			'vuln_detail_fixed_datetime = \'?s\', ' .
	    			'vuln_detail_fixed_notes =\'?s\',' .
	    			'vuln_detail_ticket =\'?s\',' .
	    			'host_id = ?i, ' .
	    			'risk_id = ?i, ' .
	    			'vuln_id = ?i',
				$this->get_text(),
				$this->get_datetime(),
	    		$this->get_ignore(),
	    		$this->get_ignore_datetime(),
	    		$this->get_ignored_user_id(),
	    		$this->get_fixed(),
	    		$this->get_fixed_user_id(),
	    		$this->get_fixed_datetime(),
	    		$this->get_fixed_notes(),
	    		$this->get_ticket(),
	    		$this->get_host_id(),
	    		$this->get_risk_id(),
	    		$this->get_vuln_id()
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
     * Set the datetime on this object
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Datetime The datetime stamp that the vuln was observed.
     * @return Boolean False if the timestamp doesnt' validate with PHP strtotime
     */
     public function set_datetime($time) {
     	$retval = FALSE;
     	if (strtotime($time)) {
    		$this->datetime = $time;
    		$retval = TRUE;
    	}
    	return $retval;
     }
    
    /**
     * Is this vulnerability fixed?
     * 
     * @access public
     * @param Boolean Whether or not this is fixed (false=no, true=yes)
     */
    public function set_fixed($fixed) {
     	$this->fixed = (bool) $fixed;
    }
    
    /**
     * Record the datetime that this vulnerability detail 
     * was marked fixed.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Datetime The timestamp this vuln was fixed.
     * @return Boolean False if the timestamp doesnt' validate with PHP strtotime
     */
    public function set_fixed_datetime($fixed_datetime) {
    	$retval = FALSE;
     	if (strtotime($fixed_datetime)) {
    		$this->fixed_datetime = $fixed_datetime;
    		$retval = TRUE;
    	}
    	return $retval;
    }
    
    /**
     * Record any notes about this vulnerability.
     * 
     * @access public
     * @param String Notes about the fix to this vuln detail.
     */
    public function set_fixed_notes($fixed_notes) {
    	$this->fixed_notes = $fixed_notes;
    }
    
    /**
     * The user who is marking this record fixed.
     * 
     * @access public
     * @param Int The ID of the User marking the record fixed.
     */
    public function set_fixed_user_id($user_id) {
    	$this->fixed_user_id = intval($user_id);
    }
    
    public function set_host_by_ip($ip) {
    	$host = new Host();
    	$host->set_ip($ip);
    	$host->lookup_by_ip();
    	$this->set_host_id($host->get_id());
    }
    
    /**
     * Set the associated Host
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Int The unique id of the associated Host object
     */
     public function set_host_id($id) {
     	$this->host_id = (int) $id;
     }
    
    /**
     * Is this vuln detail to be ignored?
     * 
     * @access public
     * @param Boolean Whether or not to ignore this record.
     */
    public function set_ignore($ignore) {
    	$this->ignore = (bool) $ignore;
    }
    
    /**
     * The date this record is being marked to be ignored.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Datetime The timestamp this record was ignored.
     * @return Boolean False if the timestamp doesnt' validate with PHP strtotime
     */
    public function set_ignore_datetime($datetime) {
    	$retval = FALSE;
     	if (strtotime($datetime)) {
    		$this->ignore_datetime = $datetime;
    		$retval = TRUE;
    	}
    	return $retval;
    }
    
    /**
     * Record the id of the User who is marking this 
     * vulnerability description to be ignored from
     * future reports.
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Int The ID of the User marking the record ignored.
     */
    public function set_ignore_user_id($user_id) {
    	$this->ignore_user_id = intval($user_id);
    }
    
    public function set_risk_id($id) {
    	$this->risk_id = intval($id);
    }
    
    /**
     * Set the text description for this vuln detail.
     * 
     * @access public
     * @param String The description text.
     */
    public function set_text($text) {
    		$this->text = $text;
    }
    
    /**
     * Set the URL to the ticket for this vuln detail.
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @access public
     * @param String The URL to the external ticket.
     * @return Boolean False if the URL doesn't validate.
     */
    public function set_ticket($ticket) {
    	$retval = FALSE;
    	if (filter_var($ticket, FILTER_VALIDATE_URL)) {
    		$this->ticket = $ticket;
    		$retval = TRUE;
    	}
    	return $retval;
    }
    
    /**
     * Set the associated Vuln id
     * 
     * @access public
     * @param Int The id of the associated Vuln object.
     */
    public function set_vuln_id($id) {
    	$this->vuln_id = intval($id);
    }

} /* end of class Vuln_detail */

?>