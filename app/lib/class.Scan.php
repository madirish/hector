<?php

error_reporting(E_ALL);

/**
 * @package HECTOR
 * @author Justin C. Klein Keane <justin@madirish.net>
 *
 * class.Scan.php is the holder object for the organization
 * of scanning schedule data (such as when to run the scan
 * and which host groups to apply it to).  The object
 * also contains information about the Scan_type object 
 * which defines the actual script to be run.
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @version .1
 */

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
 * Short description of class Scan
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @version .1
 */
class Scan extends Maleable_Object implements Maleable_Object_Interface {
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---
    private $name;
    
    private $type = null;
    
    /**
     * Id's of the host groups included in the scan
     */
    private $group_ids = array();
    
    private $daily = null; // 1 or 0
    
    private $dayofweek = null; // 1-7, 0 for no
    
    private $dayofmonth = null; // 1-32(?), 0 for no
    
    private $dayofyear = null; // 1-365(?), 0 for no

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
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
				'SELECT * FROM scan WHERE scan_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->id = $result[0]->scan_id;
			$this->name = $result[0]->scan_name;
			$this->daily = $result[0]->scan_daily;
			$this->dayofweek = $result[0]->scan_dayofweek;
			$this->dayofmonth = $result[0]->scan_dayofmonth;
			$this->dayofyear = $result[0]->scan_dayofyear;
			$this->type = new Scan_type($result[0]->scan_type_id);
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
     * @return void
     */
    public function delete() {
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM scan WHERE scan_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }
	
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
    
    /* This function directly supports the Collection class.
	 * 
	 * @return SQL select string
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
    
    public function get_daily() {
    	return $this->daily;
    }
    
    public function get_dayofmonth() {
    	return $this->dayofmonth;
    }
    
    public function get_dayofweek() {
    	return $this->dayofweek;
    }
    
    public function get_dayofyear() {
    	return $this->dayofyear;
    }
    
    public function get_friendly_daily() {
			return ($this->daily > 0) ? "Yes" : "";
		}
    
    public function get_friendly_dayofmonth() {
			return ($this->dayofmonth > 0) ? $this->dayofmonth : "";
		}
    
    public function get_friendly_dayofyear() {
			return ($this->dayofyear > 0) ? $this->dayofyear : "";
		}
    
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
		 * @return Scan type name
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
    
    public function get_group_ids() {
    	return $this->group_ids;
    }
	
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
    
    public function get_name() {
    	return $this->name;
    }
    
    /**
     * 
     * @return Scan_type
     */
    public function get_type() {
    	return $this->type;
    }
    
    public function get_scan_type_id() {
    	$retval = 0;
    	if (isset($this->type) && is_object($this->type)) $retval = $this->type->get_id();
    	return $retval;
    }
    
    public function get_type_name() {
    	$retval = '';
    	if (is_object($this->get_type()))
    		$retval = $this->get_type()->get_name();
    	return $retval;
    }
    
    public function save() {
    	if ($this->get_id() > 0 ) {
    		// Update an existing scan
	    	$sql = array(
	    		'UPDATE scan ' .
	    		'SET scan_name = \'?s\', ' .
	    			'scan_type_id = ?i, ' .
	    			'scan_daily = ?i, ' .
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
	    	$this->db->iud_sql($sql);
    	}
    	else {
    		// Insert a new value
	    	$sql = array(
	    		'INSERT INTO scan ' .
	    		'SET scan_name = \'?s\', ' .
	    			'scan_type_id = ?i, ' .
	    			'scan_daily = ?i, ' .
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
	    	$this->db->iud_sql($sql);
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
    }
    
    public function set_daily($int) {
    	$int = (int) $int;
    	if ($int != 1) $int = 0;
    	$this->daily = $int;
    }
    
    public function set_dayofmonth($int) {
    	$int = (int) $int;
    	if ($int > 32 || $int < 1) $int = 0;
    	$this->dayofmonth = $int;
    }
    
    public function set_dayofweek($int) {
    	$int = (int) $int;
    	if ($int > 7 || $int < 1) $int = 0;
    	$this->dayofweek = $int;
    }
    
    public function set_dayofyear($int) {
    	$int = (int) $int;
    	if ($int > 365 || $int < 1) $int = 0;
    	$this->dayofyear = $int;    	
    }
    
    public function set_group_ids($ids) {
    	$retval = array();
    	if (is_array($ids)) {
    		foreach ($ids as $id) {
    			$id = (int) $id;
    			if ($id > 0) $retval[] = $id;
    		}
    	}
    	$this->group_ids = $retval;
    }
    
    public function set_name($name) {
    	$this->name = htmlspecialchars($name);
    }
    
    public function set_type(Scan_type $type) {
    	$this->type = $type;
    }
    
    public function set_type_by_id($id) {
    	$this->type = new Scan_type(intval($id));
    }

} /* end of class Scan */

?>