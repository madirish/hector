<?php
/**
 *
 * class.Scan.php 
 *
 * The holder object for the organization
 * of scanning schedule data (such as when to run the scan
 * and which host groups to apply it to).  The object
 * also contains information about the Scan_type object 
 * which defines the actual script to be run.
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <justin@madirish.net>
 */
 
/**
 * Error reporting
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Scan_type.php');
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');
require_once('class.Host_group.php');

/* user defined constants */

/**
 * The generic Scan object keeps track of schedules and
 * names of Scan_type's that need to be run
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
class Scan extends Maleable_Object implements Maleable_Object_Interface {
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
     * The name of the scan
     * 
     * @access private
     * @var String The name of the scan
     */
    private $name;
    
    /**
     * The scan type
     * 
     * @access private
     * @var Scan_type The Scan_type for this scan
     */
    private $type = null;
    
    /**
     * Id's of the host groups included in the scan
     * 
     * @access private
     * @var Array An array of group_ids for this SCan
     */
    private $group_ids = array();
    
    /**
     * Whether or not this scan is scheduled daily
     * 
     * @access private
     * @var Int One for a daily scan, zero otherwise
     */
    private $daily = null; // 1 or 0
    
    /**
     * The day of the week for the scan
     * 
     * @access private
     * @var Int Day of the week Int, 0 for no, 1=Sunday, 7=Saturday
     */
    private $dayofweek = null; // 1-7, 0 for no
    
    /**
     * Day of the month to run the scan
     * 
     * @access private
     * @var Int The day of the month, 1-32, 0 for no, to run the scan
     */
    private $dayofmonth = null; // 1-32(?), 0 for no
    
    /**
     * The day of the year to run the scan
     * 
     * @access private
     * @var Int The day of the year to run the scan, 0 for no or 1-365
     */
    private $dayofyear = null; // 1-365(?), 0 for no

    // --- OPERATIONS ---

    /**
     * Construct a new instance of the scan object.
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @param  Int The unique ID
     * @return void
     */
    public function __construct($id = '') {
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM scan WHERE scan_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->set_id($result[0]->scan_id);
			$this->set_name($result[0]->scan_name);
			$this->set_daily($result[0]->scan_daily);
			$this->set_dayofweek($result[0]->scan_dayofweek);
			$this->set_dayofmonth($result[0]->scan_dayofmonth);
			$this->set_dayofyear($result[0]->scan_dayofyear);
			$this->set_type(new Scan_type($result[0]->scan_type_id));
			$sql = array(
	    		'SELECT host_group_id from scan_x_host_group where scan_id = ?i',
	    		$this->get_id()
		    );
	    	$result = $this->db->fetch_object_array($sql);
	    	if (is_array($result) && count($result) > 0) {
	    		foreach($result as $row) {
	    			$this->group_ids[] = $row->host_group_id;
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
	    		'DELETE FROM scan WHERE scan_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	$this->set_id(null);
    	}
    	return $retval;
    }
	
	/**
	 * Get the Array for the CRUD template
	 * 
	 * @acces public
	 * @return Array The Array for the CRUD template
	 */
	public function get_add_alter_form() {
		// set up the displays
		$hostgroups = array();
		$collection = new Collection('Host_group');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$hostgroups[$element->get_id()]=$element->get_name();
			}
		}
		
		$scantypes = array();
		$collection = new Collection('Scan_type');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$scantypes[$element->get_id()]=$element->get_name();
			}
		}
		
		$dayofmonth = array();
		for ($i=0;$i<33;$i++) $dayofmonth[] = $i;
		
		$dayofweek = array(0=>'','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
		
		$dayofyear = array();
		for ($i=0;$i<366;$i++) $dayofyear[] = $i;
		
		return array (
			array('label'=>'Scan name', 
					'type'=>'text', 
					'name'=>'name', 
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'Daily scan?', 
					'name'=>'daily', 
					'type'=>'select', 
					'options'=>array(0=>'No',1=>'Yes'), 
					'value_function'=>'get_daily',
					'process_callback'=>'set_daily'),
			array('label'=>'Day of week', 
					'name'=>'dayofweek', 
					'type'=>'select', 
					'options'=>$dayofweek, 
					'value_function'=>'get_dayofweek',
					'process_callback'=>'set_dayofweek'),
			array('label'=>'Day of month (1-32(?), 0 for no)', 
					'name'=>'dayofmonth', 
					'type'=>'select', 
					'options'=>$dayofmonth, 
					'value_function'=>'get_dayofmonth',
					'process_callback'=>'set_dayofmonth'),
			array('label'=>'Day of year (1-365(?), 0 for no)', 
					'name'=>'dayofyear', 
					'type'=>'select', 
					'options'=>$dayofyear, 
					'value_function'=>'get_dayofyear',
					'process_callback'=>'set_dayofyear'),
			array('label'=>'Scan type', 
					'name'=>'scantype', 
					'type'=>'select', 
					'options'=>$scantypes, 
					//'value_function'=>'get_type_name',
					'value_function'=>'get_scan_type_id',
					'process_callback'=>'set_type_by_id'),
			array('label'=>'Host groups', 
					'name'=>'hostgroups[]', 
					'type'=>'checkbox', 
					'options'=>$hostgroups, 
					//'value_function'=>'get_host_groups_readable',
					'value_function'=>'get_group_ids',
					'process_callback'=>'set_group_ids')
		);
	}
    
    /**
     * This function directly supports the Collection class.
	 * 
	 * @access public
	 * @param String The filter string for the WHERE clause
	 * @param String The ORDER BY clause for the SQL statement
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT scan_id FROM scan WHERE scan_id > 0';
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
	 * Get the Array for the display template
	 * 
	 * @access public
	 * @return Array The array for the displays template
	 */
	public function get_displays() {
		return array('Name'=>'get_name', 
					'Scan type'=>'get_type_name', 
					'Daily?'=>'get_friendly_daily', 
					'Day of week'=>'get_friendly_dayofweek', 
					'Day of month'=>'get_friendly_dayofmonth', 
					'Day of year'=>'get_friendly_dayofyear',
					'Host groups'=>'get_host_groups_readable',
					);
	}
    
    /**
     * Return whether or not this scan is daily
     * 
     * @access public
     * @return Int Zero or one.
     */
    public function get_daily() {
    	$retval = 0;
    	if (intval($this->daily) == 1) {
    		$retval = 1;
    	}
    	return $retval;
    }
    
    /**
     * Return the day of the month for the scan
     * 
     * @access public
     * @return Int Zero to 32
     */
    public function get_dayofmonth() {
    	$retval = 0;
    	if (intval($this->dayofmonth) > 32) {
    		$retval = 0;
    	}
    	else {
    		$retval = intval($this->dayofmonth);
    	}
    	return $retval;
    }
    
    /**
     * Return the day of week for the scan
     * 
     * @access public
     * @return Int From zero, for none, to 7
     */
    public function get_dayofweek() {
    	$retval = intval($this->dayofweek);
    	if ($retval < 0 || $retval > 7) {
    		$retval = 0;
    	}
    	return $retval;
    }
    
    /**
     * Return the day of the year
     * 
     * @access public
     * @return Int From zero, for none, to 366
     */
    public function get_dayofyear() {
    	$retval = intval($this->dayofyear);
    	if ($retval < 0 || $retval > 366) {
    		$retval = 0;
    	}
    	return $retval;
    }
    
    /**
     * Return the string representation for whether or
     * not we run this scan daily.
     * 
     * @access public
     * @return String 'Yes' or ''
     */
    public function get_friendly_daily() {
		return ($this->daily > 0) ? "Yes" : "";
	}
    
    /**
     * Return the friendly string representation of the dayofmonth
     * 
     * @access public
     * @return String The dayofmonth
     */
    public function get_friendly_dayofmonth() {
		return ($this->dayofmonth > 0) ? $this->dayofmonth : "";
	}
    
    /**
     * Return the friendly string representation of the dayofyear
     * 
     * @access public
     * @return String The dayofmonth
     */   
    public function get_friendly_dayofyear() {
		return ($this->dayofyear > 0) ? $this->dayofyear : "";
	}
    
    /**
     * Return the friendly string for the day of the week
     * 
     * @access public
     * @return String The name of the day of the week.
     */
    public function get_friendly_dayofweek() {
		$days =  array("", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		return $days[$this->dayofweek];
	}
		
	/**
	 * Allow flags to be set for specific scans, rather
	 * than by scan types.  This way we can set up a 
	 * generic scan type (say for web ports) and schedule
	 * scans of various host groups.
	 * 
	 * @access public
	 * @return String The flags for the scan for which groups
	 */
	public function get_group_flags() {
		$retval = '';
		switch ($this->type->get_script()) {
			case('nmap_scan.php'):
				if (count($this->group_ids)>0) 
					$retval = '-g=' . implode(',', $this->group_ids);
				break;
		}
		return $retval;
	}
    
    /**
     * Return the ids of the groups on this scan
     * 
     * @access public
     * @return Array Return the array of the group_ids for this scan
     */
    public function get_group_ids() {
    	return $this->group_ids;
    }
	
	/**
	 * Get the names for the host groups
	 * 
	 * @access public
	 * @return String A comma separated list of host group names
	 */
	public function get_host_groups_readable() {
		$retval = '';
		if (is_array($this->group_ids)) {
			foreach ($this->group_ids as $id) {
				$hg = new Host_group($id);
				if ($retval != '') $retval .= ', ';
				$retval .= $hg->get_name();
			}
		}
		return $retval;
	}
	
	/**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Scan Schedule';
    } 
    
    /**
     * Get the name of the scan
     * 
     * @access public
     * @return String The HTML display safe string
     */
    public function get_name() {
    	return htmlspecialchars($this->name);
    }
    
    /**
     * Return the Scan_type
     * 
     * @access public
     * @return Scan_type The Scan_type associated with this scan
     */
    public function get_type() {
    	if (is_a($this->type, 'Scan_type')) {
    		$retval = $this->type;
    	}
    	else {
    		$retval = new Scan_type();
    	}
    	return $retval;
    }
    
    /**
     * Get the associate id for the Scan_type
     * 
     * @access public
     * @return Int The unique ID for the Scan_type
     */
    public function get_scan_type_id() {
    	$retval = 0;
    	if (isset($this->type) && is_object($this->type)) $retval = $this->type->get_id();
    	return $retval;
    }
    
    /**
     * Return the Type name for this Scan
     * 
     * 
     * @access public
     * @return String The name of the associated Scan.
     */
    public function get_type_name() {
    	$retval = '';
    	if (is_object($this->get_type()))
    		$retval = $this->get_type()->get_name();
    	return $retval;
    }
    
    /**
     * Persist the object to the data layer.
     * 
     * @access public
     * @return Boolean False if anything goes awry
     */
    public function save() {
    	$retval = FALSE;
    	if ($this->get_id() > 0 ) {
    		// Update an existing scan
	    	$sql = array(
	    		'UPDATE scan ' .
	    		'SET scan_name = \'?s\', ' .
	    			'scan_type_id = ?i, ' .
	    			'scan_daily = ?b, ' .
	    			'scan_dayofweek = ?i, ' .
	    			'scan_dayofmonth = ?i, ' .
	    			'scan_dayofyear = ?i ' . 
	    		' WHERE scan_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_type()->get_id(),
	    		$this->get_daily(),
	    		$this->get_dayofweek(),
	    		$this->get_dayofmonth(),
	    		$this->get_dayofyear(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		// Insert a new value
	    	$sql = array(
	    		'INSERT INTO scan ' .
	    		'SET scan_name = \'?s\', ' .
	    			'scan_type_id = ?i, ' .
	    			'scan_daily = ?b, ' .
	    			'scan_dayofweek = ?i, ' .
	    			'scan_dayofmonth = ?i, ' .
	    			'scan_dayofyear = ?i',
	    		$this->get_name(),
	    		$this->get_type()->get_id(),
	    		$this->get_daily(),
	    		$this->get_dayofweek(),
	    		$this->get_dayofmonth(),
	    		$this->get_dayofyear()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = array(
	    		'SELECT scan_id FROM scan WHERE scan_name = \'?s\' AND ' .
	    			'scan_type_id = ?i AND ' .
	    			'scan_daily = ?i AND ' .
	    			'scan_dayofweek = ?i AND ' .
	    			'scan_dayofmonth = ?i AND ' .
	    			'scan_dayofyear = ?i',
	    		$this->name,
	    		$this->get_type()->get_id(),
	    		$this->get_daily(),
	    		$this->get_dayofweek(),
	    		$this->get_dayofmonth(),
	    		$this->get_dayofyear()
	    	);
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->scan_id > 0) {
	    		$this->set_id($result[0]->scan_id);
	    	}
    	}
    	
    	// Set up the groups
    	$sql = array('DELETE FROM scan_x_host_group WHERE scan_id = ?i', $this->get_id());
    	$this->db->iud_sql($sql);
    	foreach ($this->group_ids as $id) {
    		$sql = array('INSERT INTO scan_x_host_group SET scan_id = ?i, host_group_id = ?i', $this->get_id(), $id);
			$this->db->iud_sql($sql);
    	}
	    return $retval;
    }
    
    /**
     * Set whether or not this is a daily scan
     * 
     * @access public
     * @param Int Is this a daily scan, 0=No, 1=Yes
     */
    public function set_daily($int) {
        $retval = true;
    	$int = (int) $int;
    	if ($int != 1) {
    		$int = 0;
            $retval = false;
    	}
    	$this->daily = $int;
        return $retval;
    }
    
    /**
     * Set what day of the month to scan on
     * 
     * @access public
     * @param Int The day of the month to scan.
     */
    public function set_dayofmonth($int) {
        $retval = true;
    	$int = (int) $int;
    	if ($int > 32 || $int < 1) {
    		$int = 0;
            $retval = false;
    	}
    	$this->dayofmonth = $int;
        return $retval;
    }
    
    /**
     * The day of the week to scan.
     * 
     * @access public
     * @param Int The day of the week (0-7) to scan, 0=No
     */
    public function set_dayofweek($int) {
        $retval = true;
    	$int = (int) $int;
    	if ($int > 7 || $int < 1) {
    		$int = 0;
            $retval = false;
    	}
    	$this->dayofweek = $int;
        return $retval;
    }
    
    /**
     * Set the day of the year for the scan
     * 
     * @access public
     * @param Int The day of the year for the scan
     */
    public function set_dayofyear($int) {
        $retval = false;
    	$int = (int) $int;
    	if ($int > 365 || $int < 1) {
    		$int = 0;
            $retval = false;
    	}
    	$this->dayofyear = $int;   
        return $retval; 	
    }
    
    /**
     * Set the group id's to scan
     * 
     * @access public
     * @param Array An array of the group id's that should be scanned
     */
    public function set_group_ids($ids) {
        $retval = true;
    	$groupids = array();
    	if (is_array($ids)) {
    		foreach ($ids as $id) {
    			$id = (int) $id;
    			if ($id > 0) $groupids[] = $id;
                else $retval = false;
    		}
    	}
    	$this->group_ids = $groupids;
        return $retval;
    }
    
    /**
     * Set the name of this scan
     * 
     * @access public
     * @param String The name of the scan.
     */
    public function set_name($name) {
    	$this->name = $name;
    }
    
    /**
     * Set the type of the scan
     * 
     * @access public
     * @param Scan_type The Scan_type for this scan
     */
    public function set_type(Scan_type $type) {
        $retval = true;
        if (is_a($type, 'Scan_type')) $this->type = $type;
        else $retval = false;
        return $retval;
    }
    
    /**
     * Set the type for the Scan
     * 
     * @access public
     * @param Int The unique ID for the Scan_type
     */
    public function set_type_by_id($id) {
    	$this->type = new Scan_type(intval($id));
    }

} /* end of class Scan */

?>