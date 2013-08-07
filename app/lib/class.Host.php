<?php

error_reporting(E_ALL);

/**
 * class.Host.php
 *
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include Port
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
require_once('class.Nmap_scan_result.php');

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');
require_once('class.Host_group.php');
require_once('class.Supportgroup.php');
require_once('class.Location.php');
require_once('class.Tag.php');

/**
 * Hosts are the crux of the system
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @todo enable MAC address tracking
 */
class Host extends Maleable_Object implements Maleable_Object_Interface {
	// --- ASSOCIATIONS ---
	
	
	// --- ATTRIBUTES ---
	/**
	 * Short description of attribute ip
	 *
	 * @access private
	 * @var String
	 */
	private $ip = null;
	
	/**
	 * Name of the host
	 * 
	 * @access private
	 * @var String
	 */
	private $name;
	
	/**
	 * Operating system of the host
	 * 
	 * @access private
	 * @var String
	 */
	private $os = null;
	
	/**
	 * Ports that are detected on the host
	 *
	 * @access private
	 * @var array
	 */
	private $ports = null;
	
	/**
	 * Host groups to which this host belongs
	 *
	 * @access private
	 * @var array
	 */
	private $host_group_ids = array();
	
	/**
	 * Tags applied to this host
	 *
	 * @access private
	 * @var array
	 */
	private $tag_ids = array();
	
	/**
	 * Alternative hostnames for the host
	 *
	 * @access private
	 * @var array
	 */
	private $alt_hostnames = array();
	
	/**
	 * Alternative IP addresses for the host
	 *
	 * @access private
	 * @var array
	 */
	private $alt_ips = array();
	
	/**
	 * Contact for the host
	 *
	 * @access private
	 * @var String
	 */
	private $sponsor = "";
	
	/**
	 * Technical contact for the host
	 *
	 * @access private
	 * @var String
	 */
	private $technical = "";
	
	/**
	 * Support group object
	 * 
	 * @var Supportgroup
	 * @access private
	 */
	private $supportgroup = "";
	
	/**
	 * Location object
	 * 
	 * @access private
	 * @var Location
	 */
	private $location = "";
	
	/**
	 * Horrible, terrible, free form note field
	 * that we hope will never be used.
	 *
	 * @access private
	 * @var String
	 */
	private $note = "";
	
	/**
	 * Is the machine governed by policy
	 * (i.e. does it contain confidential data)
	 * 
	 * @access private
	 * @var boolean
	 */
	private $policy = "";
	
	/**
	 * Link to an external reference on the machine
	 * 
	 * @access private
	 * @var String
	 */
	private $link;
	
	/**
	 * Array of host's urls and screenshot filenames (url_url, url_screenshot)
	 * 
	 * @access private
	 * @var array
	 */
	private $urls = array();
	
	/**
	 * Exclude this host from future port scans?
	 *
	 * @access private
	 * @var boolean
	 */
	private $ignore_portscan = NULL;
	
	/**
	 * User id of the person who excluded this host
	 * from portscans
	 *
	 * @access private
	 * @var int
	 */
	private $ignore_portscan_byuserid = NULL;
	
	/**
	 * How many days to exclude this host from future
	 * port scans
	 *
	 * @access private
	 * @var int
	 */
	private $ignoredfor_days = NULL;
	
	/**
	 * Time an exclusion was put into place (for 
	 * removing this host from port scans)
	 *
	 * @access private
	 * @var int
	 */
	private $ignored_timestamp = NULL;
	
	/**
	 * Horrible, aweful, freeform text field that 
	 * should include some explaination of why this
	 * host shouldn't be scanned.
	 *
	 * @access private
	 * @var String
	 */
	private $ignored_note = NULL;
	
	// --- OPERATIONS ---
	
	/**
	 * Create a new Host object
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  int id
	 * @return void
	 */
	public function __construct($id = '', $minimal = 'no') {
		global $appuser;
		global $scriptrun;
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sqlRestrict = ($minimal == 'no') ?
				'*' :
				'h.host_id, h.host_ip, h.host_name';
			if (isset($appuser) && ! $appuser->get_is_admin()) {
				$sql = array(
					'SELECT '.$sqlRestrict.' from host h, user_x_supportgroup x ' .
					'where x.user_id = ' . $appuser->get_id() . ' AND ' .
							'h.supportgroup_id = x.supportgroup_id AND ' .
							'host_id = ?i',
					$id
				);
			}
			else {
				$sql = array(
					'SELECT '.$sqlRestrict.' from host h where h.host_id = ?i',
					$id
				);
			}
			$result = $this->db->fetch_object_array($sql);
			if (is_array($result) && is_object($result[0])) {			
				$this->id = $result[0]->host_id;
				$this->ip = $result[0]->host_ip;
				$this->name = $result[0]->host_name;
				if ($minimal == 'no') {
					$this->os = $result[0]->host_os;
					$this->sponsor = $result[0]->host_sponsor;
					$this->link = $result[0]->host_link;
					$this->note = $result[0]->host_note;
					$this->supportgroup = new Supportgroup($result[0]->supportgroup_id);
					$this->location = new Location($result[0]->location_id);
					$this->technical = $result[0]->host_technical;
					$this->policy = $result[0]->host_policy;
					$this->ignore_portscan = $result[0]->host_ignore_portscan;
					$this->ignore_portscan_byuserid = $result[0]->host_ignoredby_user_id;
					$this->ignoredfor_days = $result[0]->host_ignoredfor_days;
					$this->ignored_timestamp = $result[0]->host_ignored_timestamp;
					$this->ignored_note = $result[0]->host_ignored_note;
					$sql= array('SELECT url_url, url_screenshot from url where host_id = ?i', $id);
					$results = $this->db->fetch_object_array($sql);
					foreach($results as $result){
						$this->urls[] = array($result->url_url, $result->url_screenshot);
					}
				}
				// Is there an exclusion?  Should it be honored?
				if ($this->ignore_portscan) {
					$this->check_expire_scan_exclusion();
				}
				
				// Only run these queries if necessary
				if ($minimal == 'no') {
					// Populate the host groups
					$sql = array(
						'SELECT x.host_group_id from host_x_host_group x, host_group g ' .
						'WHERE g.host_group_id = x.host_group_id ' .
						'AND x.host_id = ?i order by g.host_group_name',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						foreach ($result as $record) $this->host_group_ids[] = $record->host_group_id;
					}
					// Populate the alterantive names
					$sql = array(
						'SELECT host_alt_name from host_alts WHERE host_id = ?i',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						foreach ($result as $record) $this->alt_hostnames[] = $record->host_alt_name;
					}
					// Populate the alternative IP's
					$sql = array(
						'SELECT host_alt_ip from host_alts WHERE host_id = ?i',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						foreach ($result as $record) $this->alt_ips[] = $record->host_alt_ip;
					}
					// Populate the tags
					$sql = array(
						'SELECT tag_id from host_x_tag WHERE host_id = ?i',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						foreach ($result as $record) $this->tag_ids[] = $record->tag_id;
					}
				}
			}
			else {
				$this->log->write_error("Unable to fetch array for Host object id $id.  Corrupt MySQL?");
			}
		}
	}

	/**
	 * Add this host to a host group.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param int
	 */
	public function add_host_group_id($id) {
		$id = intval($id);
		if (! in_array($id, $this->host_group_ids) && $id > 0){
			$this->host_group_ids[] = $id;
		}
	}
	
	/**
	 * If this host is being excluded from portscans, make
	 * sure that the exclusion is still valid.  If it has
	 * expired update the record accordingly.
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return true
	 */
	private function check_expire_scan_exclusion() {
		if ($this->ignoredfor_days > 0) {
			$sql = 'select datediff(' .
											'date_add(host_ignored_timestamp, ' .
											'INTERVAL host_ignoredfor_days DAY), now()) ' .
											'as exclude from host where host_id = ' . $this->id;
			$active_exclude = $this->db->fetch_object_array($sql);
			if (is_array($active_exclude) && $active_exclude[0]->exclude < 0) {
				// Exclusion has expired
				$this->log->write_message("Expiring portscan exclusion for host id " + $this->id);
				$this->set_portscan_exclusion(0);
				$sql = 'update host set host_ignore_portscan = 0 ' .
						'where host_id = ' . $this->id;
				$this->db->iud_sql($sql);
			}
		}
	}
	
	/**
	 * Check the ip to make sure it doesn't contain
	 * any illegal characters and is of the correct
	 * format.
	 *
	 * @access private
	 * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
	 * @param IP address
	 * @return boolean
	 */
	function check_ip($ip) {
			if (! ip2long($ip)) return false;
			else return true;
	}

	/**
	 * This function is designed to avoid collisions
	 * in the data storage.  If the host is assigned
	 * the same IP as another host or the same name
	 * as another host this check will return false.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return boolean
	 */
	public function check_save() {
		$retval = true;
		$sql = array(
			'(SELECT host_id from host WHERE host_id != ?i AND host_ip = \'?s\') ' .
			'UNION' .
			'(SELECT host_id FROM host_alts WHERE host_id != ?i AND ' .
				'( host_alt_ip = \'?s\' OR ' .
				'  host_alt_ip IN (select host_alt_ip from host_alts where host_id = ?i)))',
			$this->id,
			$this->ip,
			$this->id,
			$this->ip,
			$this->id
		);
		$result = $this->db->fetch_object_array($sql);
		if (is_array($result)) {
			foreach ($result as $record) {
				if (intval($record->host_id) > 0) {
					$this->error = 'Duplicate IP exists.';
					$this->log->write_error('Duplicate IP (' . $this->get_ip . 
						') exists from host id ' . $this->get_id());
					$retval = false;
				}
			}
		}
		$sql = array(
			'(SELECT host_id from host WHERE host_id != ?i AND host_name = \'?s\') ' .
			'UNION' .
			'(SELECT host_id FROM host_alts WHERE host_id != ?i AND ' .
				'( host_alt_name = \'?s\' OR ' .
				'  host_alt_name IN (select host_alt_name from host_alts where host_id = ?i)))',
			$this->id,
			$this->name,
			$this->id,
			$this->name,
			$this->id
		);
		$result = $this->db->fetch_object_array($sql);
		if (is_array($result)) {
			foreach ($result as $record) {
				if (intval($record->host_id) > 0) {
					$this->error = 'Duplicate name exists.';
					$this->log->write_error('Duplicate IP (' . $this->get_ip . 
						') exists from host id ' . $this->get_id());
					$retval = false;
				}
			}
		}
		return $retval;
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
	    		'DELETE FROM host WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

	/**
	 * Render the add or edit form for the interface.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 */
	public function get_add_alter_form() {
		// get the host groups array
		$hostgroups = array();
		$collection = new Collection('Host_group');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$hostgroups[$element->get_id()]=$element->get_name();
			}
		}
		// get the Support groups array
		$supportgroups = array('0'=>'');
		$collection = new Collection('Supportgroup');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$supportgroups[$element->get_id()]=$element->get_name();
			}
		}
		// get the Locations array
		$locations = array('0'=>'');
		$collection = new Collection('Location');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$locations[$element->get_id()]=$element->get_name();
			}
		}
		// get the Tags array
		$tags = array();
		$collection = new Collection('Tag');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$tags[$element->get_id()]=$element->get_name();
			}
		}

		return array (
			array('label'=>'Host name',
					'type'=>'text',
					'name'=>'hostname',
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'IP',
					'name'=>'ip',
					'type'=>'text',
					'value_function'=>'get_ip',
					'process_callback'=>'set_ip'),
			array('label'=>'Operating System',
					'name'=>'os',
					'type'=>'text',
					'value_function'=>'get_os',
					'process_callback'=>'set_os'),
			array('label'=>'Host groups',
					'name'=>'hostgroups[]',
					'type'=>'checkbox',
					'options'=>$hostgroups,
					'value_function'=>'get_host_group_ids',
					'process_callback'=>'set_host_group_ids'),
			array('label'=>'Support Group',
					'name'=>'supportgroup[]',
					'type'=>'select',
					'options'=>$supportgroups,
					'value_function'=>'get_supportgroup_id',
					'process_callback'=>'set_supportgroup_id'),
			array('label'=>'Location',
					'name'=>'location[]',
					'type'=>'select',
					'options'=>$locations,
					'value_function'=>'get_location_id',
					'process_callback'=>'set_location_id'),
			array('label'=>'Sponsor',
					'name'=>'sponsor',
					'type'=>'text',
					'value_function'=>'get_sponsor',
					'process_callback'=>'set_sponsor'),
			array('label'=>'Technical contact',
					'name'=>'technical',
					'type'=>'text',
					'value_function'=>'get_technical',
					'process_callback'=>'set_technical'),
			array('label'=>'Notes',
					'name'=>'note',
					'type'=>'text',
					'value_function'=>'get_note',
					'process_callback'=>'set_note'),
			array('label'=>'External URL (for more info)',
					'name'=>'link',
					'type'=>'text',
					'value_function'=>'get_link',
					'process_callback'=>'set_link'),
			array('label'=>'Falls under Computer Policy?', 
					'name'=>'policy', 
					'type'=>'select', 
					'options'=>array(0=>'No',1=>'Yes'), 
					'value_function'=>'get_policy',
					'process_callback'=>'set_policy'),
			array('label'=>'Tags',
					'name'=>'tags[]',
					'type'=>'checkbox',
					'options'=>$tags,
					'value_function'=>'get_tag_ids',
					'process_callback'=>'set_tag_ids'),
			array('label'=>'Exclude from portscan?',
					'name'=>'exclude',
					'type'=>'select', 
					'options'=>array(0=>'No',1=>'Yes'), 
					'value_function'=>'get_portscan_exclusion',
					'process_callback'=>'set_portscan_exclusion'),
			array(
					'name'=>'excludedby',
					'type'=>'hidden', 
					'value_function'=>'get_excludedby',
					'process_callback'=>'set_excludedby'),
			array('label'=>'Exclude for time period (starting now):', 
					'name'=>'excludedfor', 
					'type'=>'select', 
					'options'=>array(0=>'No exclusion',1=>'1 Day',7=>'One Week',30=>'One month',-1=>'Forever'), 
					'value_function'=>'get_excludedfor',
					'process_callback'=>'set_excludedfor'),
			array('label'=>'Reason for exclusion:',
					'name'=>'reason',
					'type'=>'text',
					'value_function'=>'get_excludedreason',
					'process_callback'=>'set_excludedreason'),
		);
	}

	/**
	 * Get alternative hostnames for this host.
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return array
	 */
	private function get_alt_hostnames() {
		return $this->alt_hostnames;
	}

	/**
	 * Get alternative IP addresses for this host.
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return array
	 */
	private function get_alt_ips() {
		return $this->alt_ips;
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT h.host_id, INET_ATON(h.host_ip) AS ipnum ' .
				'	FROM host h';
		global $appuser;
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			// Using this object via the web
			$sql .= ', user_x_supportgroup x WHERE x.user_id=' . $appuser->get_id() .
				' AND x.supportgroup_id = h.supportgroup_id ';
		}
		else {
			$sql .= ' WHERE h.host_id > 0';
		}
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
			$sql .= ' ORDER BY ipnum, h.host_name';
		}
		return $sql;
	}
	
    /**
     * This function directly supports the Collection class.
     * Gets a collection based on a port filter.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return SQL select string
	 */
	public function get_collection_by_port($port, $orderby='') {
		global $appuser;
		$portnum = intval($port);
		$sql = 'select h.host_id from host h, nmap_scan_result n ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= ', user_x_supportgroup x ';
		}
		$sql .= 'where n.host_id = h.host_id and ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= 'h.supportgroup_id = x.supportgroup_id AND' .
					'x.user_id = ' . $appuser->get_id() . ' AND ';
		}
		$sql .= 'n.nmap_scan_result_port_number = ' . $portnum .' and n.state_id=1';
		return $sql; 
	}
	
	/**
     * This function directly supports the Collection class.
     * Gets a collection based on a service version filter.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return SQL select string
	 */
	public function get_collection_by_version($version, $orderby='') {
		global $appuser;
		$portnum = strtolower(mysql_real_escape_string($version));
		$sql = 'select h.host_id from host h, nmap_scan_result n ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= ', user_x_supportgroup x ';
		}
		$sql .= 'where n.host_id = h.host_id and ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= 'h.supportgroup_id = x.supportgroup_id AND' .
					'x.user_id = ' . $appuser->get_id() . ' AND ';
		}
		$sql .= 'LOWER(n.service_version) LIKE \'%' . $version . '%\'';
		return $sql; 
	}

	/**
	 * The method to return the HTML for the details on this specific host.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return HTML chunk
	 */
	public function get_details() {
		require_once('class.Nmap_scan_result.php');
		//$scans = new Collection('Nmap_scan_result', ' and host_id = ' . $this->id . ' and s.state_state != \'closed\'', '', 'ORDER BY nmap_scan_result_port_number');
		$scans = new Collection('Nmap_scan_result', ' AND nsr.host_id = ' . $this->id, '', 'GROUP BY nsr.nmap_scan_result_protocol ORDER BY nsr.nmap_scan_result_port_number');
		$retval = '<div class="row"><div class="span5">';
		$retval .= '<table id="host_details" class="table">' . "\n";
		$retval .= '<tr id="name"><td>Hostname</td><td>' . $this->get_name() . '</td></tr>' . "\n";
		$retval .= '<tr id="ip"><td>IP Address</td><td>' . $this->get_ip() . '</td></tr>' . "\n";
		$retval .= '<tr id="ip"><td>Operating System</td><td>' . $this->get_os() . '</td></tr>' . "\n";
		$retval .= '<tr id="technical"><td>Technical contact:</td><td>' . $this->get_technical() . '</td></tr>' . "\n";
		$retval .= '<tr id="sponsor"><td>Sponsor:</td><td>' . $this->get_sponsor() . '</td></tr>' . "\n";
		$retval .= '<tr id="location"><td>Location:</td><td>' . $this->location->get_name() . '</td></tr>' . "\n";
		$retval .= '<tr id="supportgroup"><td>Support Group:</td><td>' . $this->supportgroup->get_name() . '</td></tr>' . "\n";
		$retval .= '<tr id="link"><td>External URL:</td><td><a href="' . $this->get_link() . '">' 
			. $this->get_link() . '</a></td></tr>' . "\n";
		$retval .= '<tr id="notes"><td>Notes:</td><td>' . $this->get_note() . '</td></tr>' . "\n";
		$retval .= '<tr id="policy"><td>Covered by policy:</td><td>';
		$retval .= ($this->get_policy()) ? 'Yes' : 'No';
		$retval .= '</td></tr>' . "\n";
		$retval .= '<tr id="policy"><td>Excluded from portscan alerts?:</td><td>';
		$retval .= ($this->get_portscan_exclusion()) ? 'Yes' : 'No';
		$retval .= '</td></tr>' . "\n";
		$retval .= '<tr id="notes"><td>Tags:</td><td>';
		$retval .= implode(',', $this->get_tag_names());
		$retval .= '</td></tr>' . "\n";
		if ($this->get_portscan_exclusion()) {
			$retval .= '<tr id="excludedby"><td>Excluded by:</td><td>' . $this->get_excludedby_name() . '</td></tr>' . "\n";
			$retval .= '<tr id="excludedon"><td>Excluded on:</td><td>' . $this->get_excludedon() . '</td></tr>' . "\n";
			$excludedfor = ($this->get_excludedfor() < 0) ? 'forever' : $this->get_excludedfor() . ' days';
			$retval .= '<tr id="excludedfor"><td>Excluded for:</td><td>' . $excludedfor . '</td></tr>' . "\n";
			$retval .= '<tr id="excludedreason"><td>Reason:</td><td>' . $this->get_excludedreason() . '</td></tr>' . "\n";
		}
		$retval .= '<tr id="groups"><td><a href="?action=details&object=host_group">Host groups</a>:</td><td>' . $this->get_host_groups_readable() . '</td></tr>' . "\n";
		$retval .= '</table>';
		$retval .= '</div><div class="span6"><p class="well well-small">NMAP scan results:</p>' . "\n";
		$retval .= '<table class="table table-striped"><thead>';
		$retval .= '<tr><th>Port</th><th>State</th><th>Date</th><th>Protocol</th><th>Version</th></tr>';
		$retval .= '</thead><tbody>';
		if (isset($scans->members) && is_array($scans->members)) {
			foreach ($scans->members as $scan) $retval .= $scan->get_details();
		}
		$retval .= '</tbody></table>';
		$retval .= '</div></div>';
		$retval .= '<div><table id="screenshotstable" class="table table-striped">' . "\n";
		$retval .= '<thead><tr><th>URL</th><th>Screenshot</th></tr></thead><tbody>';
		$approot = getcwd() . '/../app/';
		foreach($this->get_urls() as $url) {
			$retval .= '<tr><td>' . $url[0] . '</td><td>';
			if ($url[1] and file_exists($approot . 'screenshots/' . $url[1]))  {
				$retval .= '<a href=\'?action=display_screenshot&ajax&url=' . urlencode($url[0]) . '\'>';
				$retval .= '<img width=150 alt="No image found" src=\'?action=display_screenshot&ajax&url=' . urlencode($url[0]) . '\'></img></a>';
				
			}
			else { 
				$retval .='No image available';
				$sql = array('update url set url_screenshot=NULL where url_url=\'?s\'',$url[0]);
				$this->db->iud_sql($sql); 
			}
		}
			$retval .= '</td></tr>';
		$retval .= '</tbody></table>';
		$retval .= '</div>' . "\n";
		return $retval;
	}

	/**
	 * Get information for display via a template.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return HTML chunk
	 */
	public function get_displays() {
		return array('Name'=>'get_name_linked', 'IP'=>'get_ip', 'OS'=>'get_os', 'Host groups'=>'get_host_groups_readable');
	}

	/**
	 * Return the User object for the user who excluded this
	 * host from port scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return User
	 */
	public function get_excludedby() {
		global $appuser;
		$user = new User($this->ignore_portscan_byuserid);
		$uid = ($user->get_id() == "") ? $appuser->get_id() : $user->get_id();
		return ($this->get_portscan_exclusion()) ? $uid : '';
	}

	/**
	 * Return the username for the user who excluded this
	 * host from port scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_excludedby_name() {
		global $appuser;
		$user = new User($this->ignore_portscan_byuserid);
		return ($user->get_name());
	}

	/**
	 * Return date when this host was excluded from port
	 * scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return timestamp
	 */
	public function get_excludedon() {
		return $this->ignored_timestamp;
	}

	/**
	 * Return the number of days this host was excluded from scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int
	 */
	public function get_excludedfor() {
		return $this->ignoredfor_days;
	}

	/**
	 * Report the reason this host was excluded from scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_excludedreason() {
		return htmlspecialchars($this->ignored_note);
	}


	/**
	 * Return the Host_group id numbers for host groups that
	 * this Host belongs to.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return array
	 */
	public function get_host_group_ids() {
		return $this->host_group_ids;
	}


	/**
	 * Return a string of Host_group names for the host groups
	 * that include this Host.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_host_groups_readable() {
		$retval = '';
		if (is_array($this->host_group_ids)) {
			foreach ($this->host_group_ids as $id) {
				$hg = new Host_group($id);
				if ($retval != '') $retval .= ', ';
				$retval .= $hg->get_name();
			}
		}
		return $retval;
	}

	/**
	 * Return the unique ID for this host
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return int
	 */
	public function get_id() {
	   return intval($this->id);
	}
	
	/**
	 * Should this host be exempted from portscans?
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return boolean
	 */
	public function get_ignore_portscan() {
		return intval($this->ignore_portscan);
	}
	/**
	 * Get the host's IP address
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return ip
	 */
	public function get_ip() {
		return $this->ip;
	}
	/**
	 * Get any link to external resources about this Host
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return ip
	 */
	public function get_link() {
		return htmlspecialchars($this->link);
	}
	/**
	 * Return the location_id for the Location of the host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int
	 */
	public function get_location_id() {
		return (is_object($this->location)) ? $this->location->get_id() : null;
	}
	
	/**
	 * Return the hostname for this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_name() {
		$retval = null;
		if ($this->name != null) $retval = $this->name;
		else if ($this->ip != null) {
			$retval = gethostbyaddr($this->ip);
			$this->set_name($retval);
		}
		return strtolower($retval);
	}
	
	/**
	 * Return the hostname as a hypterlinked chunk of 
	 * HTML to insert into display so that the name can
	 * be clicked to view details.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return HTML string
	 */
	public function get_name_linked() {
		$retval = null;
		if ($this->ip != null) {
			$name = gethostbyaddr($this->ip);
			$this->set_name($name);
		}
		$retval = '<a href="?action=details&object=host&id=' .
			$this->get_id() . '">' .
			$this->get_name() . '</a>';
		return $retval;
	}
	
	/**
	 * Return any notes for this Host, hopefully this won't
	 * be used very often.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_note() {
		return htmlspecialchars($this->note);
	}
	
	/**
	 * Should this host be excluded from port scans?
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return boolean
	 */
	public function get_portscan_exclusion() {
		return (boolean) $this->ignore_portscan;
	}
	
	/**
	 * Get the number of open ports.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return int
	 */
	public function get_open_ports() {
		$retval = 0;
		$sql = array('select count(nmap_scan_result_id) as portcount ' .
				'from nmap_scan_result ' .
				'where host_id = ?i ' .
				'and state_id = 1',
				$this->id
				);
				$result = $this->db->fetch_object_array($sql);
				if (is_array($result) && isset($result[0])) {
					$retval = $result[0]->portcount;
				}
				return $retval;
	}
	
	/**
	 * Get the operating system for this host.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_os() {
		return $this->os;
	}
	/**
	 * Is this host covered by policy?  Meaning does this
	 * host have sensitive data.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return boolean
	 */
	public function get_policy() {
		return (boolean) $this->policy;
	}
	/**
	 * Get a collection of ports as Nmap_scan_result 
	 * objects using the Collection factory.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return array
	 */
	public function get_ports() {
		if ($this->ports == null) {
			$ports = new Collection('Nmap_scan_result', 'and nsr.host_id = ' . $this->get_id() . ' order by nsr.nmap_scan_result_port_number asc');
			if (isset($ports->members) && is_array($ports->members)) {
				foreach ($ports->members as $port) {
					$this->ports[] = $port;
				}
			}
		}
		return $this->ports;
	}
	
	/**
	 * The faculty or staff sponsor for this host.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_sponsor() {
		return htmlspecialchars($this->sponsor);
	}
	
	/**
	 * ID for the Support_groups to which this
	 * host is assigned.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return int
	 */
	public function get_supportgroup_id() {
		return (is_object($this->supportgroup)) ? $this->supportgroup->get_id() : null;
	}
	
	/**
	 * The technical contact for this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String
	 */
	public function get_technical() {
		return htmlspecialchars($this->technical);
	}
	
	/**
	 * Support function to allow for form submission (POST)
	 * processing.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 */
	public function process_form($attribute, $value) {
		$this->$attribute($value);
		$this->save();
	}
	
	/**
	 * Get a list of id's for tags associated with this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array of integers
	 */
	public function get_tag_ids() {
		return $this->tag_ids;
	}
	
	/**
	 * Get a list of names
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array of strings
	 */
	public function get_tag_names() {
		$names = array();
		foreach ($this->tag_ids as $tag_id) {
			$tag = new Tag($tag_id);
			$names[] = $tag->get_name();
		}
		return $names;
	}

	/**
	 * Get a list of URLs
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array of strings
	 */
	public function get_urls() {
		return $this->urls;
	}

	/**
	 * Remove this host form specificed host group
	 * 
	 * @param host_group_id
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access public
	 */
	public function remove_host_group_id($id) {
		$id = intval($id);
		$temparray = array();
    	if (in_array($id, $this->host_group_ids) && $id > 0){
    		while (count($this->host_group_ids) > 0) {
    			$item = array_shift($this->host_group_ids);
    			if ($item != $id) $temparray[] = $item;
    		}
    		$this->set_host_group_ids($temparray);
    	}
	}

	/**
	 * Save this Host, for persistence
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 */
	public function save() {
		global $appuser;
		// Check for duplicate records
		if (! $this->check_save()) return false;
		if ($this->id > 0 ) {
			// Update an existing user
	    	$sql = array(
	    		'UPDATE host SET host_ip = \'?s\', ' .
	    		  'host_ip_numeric = inet_aton(host_ip), ' .
	    			'host_os = \'?s\', ' .
	    			'host_name = \'?s\', ' .
	    			'host_sponsor = \'?s\', ' .
	    			'host_technical = \'?s\', ' .
	    			'location_id = \'?i\', ' .
	    			'supportgroup_id = \'?i\', ' .
	    			'host_note = \'?s\', ' .
	    			'host_policy = \'?i\', ' .
	    			'host_link = \'?s\', ' .
	    			'host_ignore_portscan = \'?i\', ' .
	    			'host_ignoredby_user_id = \'?i\', ' .
	    			'host_ignoredfor_days = \'?i\', ' .
	    			'host_ignored_timestamp = now(), ' .
	    			'host_ignored_note = \'?s\' ' .
	    		'WHERE host_id = \'?i\'',
	    		$this->get_ip(),
	    		$this->os,
	    		$this->get_name(),
	    		$this->get_sponsor(),
	    		$this->get_technical(),
	    		$this->get_location_id(),
	    		$this->get_supportgroup_id(),
	    		$this->get_note(),
	    		$this->get_policy(),
	    		$this->get_link(),
	    		$this->get_portscan_exclusion(),
	    		$this->get_excludedby(),
	    		$this->get_excludedfor(),
	    		$this->get_excludedreason(),
	    		$this->id
	    	);
	    	$this->db->iud_sql($sql);
		}
		else {
			// Insert a new value
	    	$sql = array(
	    		'INSERT INTO host SET host_ip = \'?s\', ' .
	    		  'host_ip_numeric = inet_aton(host_ip), ' .
	    			'host_name = \'?s\', ' .
	    			'host_os = \'?s\', ' .
	    			'host_sponsor = \'?s\', ' .
	    			'host_technical = \'?s\', ' .
	    			'location_id = \'?i\', ' .
	    			'supportgroup_id = \'?i\', ' .
	    			'host_policy = \'?i\', ' .
	    			'host_note = \'?s\', ' .
	    			'host_link = \'?s\', ' .
	    			'host_ignore_portscan = \'?i\', ' .
	    			'host_ignoredby_user_id = \'?i\', ' .
	    			'host_ignoredfor_days = \'?i\', ' .
	    			'host_ignored_timestamp = now(), ' .
	    			'host_ignored_note = \'?s\' ',
	    		$this->get_ip(),
	    		$this->get_name(),
	    		$this->get_os(),
	    		$this->get_sponsor(),
	    		$this->get_technical(),
	    		$this->get_location_id(),
	    		$this->get_supportgroup_id(),
	    		$this->get_policy(),
	    		$this->get_note(),
	    		$this->get_link(),
	    		$this->get_portscan_exclusion(),
	    		$this->get_excludedby(),
	    		$this->get_excludedfor(),
	    		$this->get_excludedreason(),
	    	); 
	    	$this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = array(
	    		'SELECT host_id FROM host WHERE host_ip = \'?s\' AND host_os = \'?s\' AND host_name = \'?s\'',
	    		$this->ip,
	    		$this->os,
	    		$this->name
	    	);
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->host_id > 0) {
	    		$this->set_id($result[0]->host_id);
	    	}
		}
	
		// Set/save the host groups (if any)
		$sql = array(
			'DELETE FROM host_x_host_group WHERE host_id = ?i',
			$this->get_id()
		);
		$this->db->iud_sql($sql);
		if (is_array($this->get_host_group_ids()) && count($this->get_host_group_ids()) > 0) {
			foreach ($this->get_host_group_ids() as $gid) {
				$sql = array('INSERT INTO host_x_host_group SET host_id = ?i, host_group_id = ?i',
				$this->get_id(),
				$gid);
				$this->db->iud_sql($sql);
			}
		}
	
		// Set/save the tags (if any)
		$sql = array(
			'DELETE FROM host_x_tag WHERE host_id = ?i',
			$this->get_id()
		);
		$this->db->iud_sql($sql);
		if (is_array($this->get_tag_ids()) && count($this->get_tag_ids()) > 0) {
			foreach ($this->get_tag_ids() as $tid) {
				$sql = array('INSERT INTO host_x_tag SET host_id = ?i, tag_id = ?i',
				$this->get_id(),
				$tid);
				$this->db->iud_sql($sql);
			}
		}
	}

	/**
	 * Set the alternate host name
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Name string
	 * @return void
	 */
    public function set_alt_hostname($name) {
    	$this->alt_hostnames[] = htmlspecialchars($name);
    }

	/**
	 * Set the alternate host ip
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param IP string
	 * @return void
	 */
    public function set_alt_ip($ip) {
    	if ($this->check_ip($ip)) {
    		$this->alt_ips[] = $ip;
    	}
    }
    /**
	 * Setter for the exclusion period
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param int $days
	 * @return void
	 */
	private function set_excludedfor($days) {
		$this->ignoredfor_days = intval($days);
	}
	
    /**
	 * Setter for the exclusion notes
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param String $note
	 * @return void
	 */
	private function set_excludedreason($note) {
		$this->ignored_note = htmlspecialchars($note);
	}
	
    /**
	 * Setter for the user id who excluded this host
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param int $id
	 * @return void
	 */
	private function set_excludedby($id) {
		$this->ignored_portscan_byuserid = intval($id);
	}

	/**
	 * Set the host groups for this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Array of Host_group ids
     * @return void
	 */
    public function set_host_group_ids($array) {
    	//sanitize the array
    	$array = array_map('intval', $array);
    	$this->host_group_ids = $array;
    }

	/**
	 * Set the IP for this host
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  ip
	 * @return void
	 * @todo Validate the IP
	 */
	public function set_ip($ip) {
		if ($this->check_ip($ip)) {
			$this->ip = $ip;
		}
	}
		
	/**
	 * Set the link to external documentation of this host.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  String $link
	 * @return void
	 */
	public function set_link($link) {
		$this->link = $link;
	}
	
	/**
	 * Set the location id
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Location id
	 * @return void
	 */
	public function set_location_id($id) {
    	$id = intval($id[0]);
    	if ($id > 0) $this->location = new Location($id);
			else $this->location = null;
	}

	/**
	 * Set the Support group id
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Supoprtgroup id
	 * @return void
	 */
    public function set_supportgroup_id($id) {
    	$id = intval($id[0]);
    	if ($id > 0) $this->supportgroup = new Supportgroup($id);
			else $this->supportgroup = null;
    }

	/**
	 * Set the host name
	 * @param Name string
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access public
	 */
    public function set_name($name) {
    	if ($name != '')
    		$this->name = htmlspecialchars($name);
    	elseif ($name == '')
    		$this->name = '';
    }

	/**
	 * Set the host note
	 * @param Note string
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access public
	 */
    public function set_note($name) {
    	if ($name != '')
    		$this->note = htmlspecialchars($name);
    	elseif ($name == '')
    		$this->note = '';
    }
    
    /**
	 * Setter to exclude this host from portscans.
	 * 
	 * @param boolean $val
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access private
	 */
	private function set_portscan_exclusion($val) {
		$this->ignore_portscan = ((boolean) $val) ? 1 : 0;
		if (isset($appuser)) $this->set_excludedby($appuser->get_id());
		if ((boolean) $val === FALSE) {
			// Unsetting the ignore so clear associated data
			$this->ignore_portscan_byuserid = NULL;
			$this->ignored_noe = NULL;
			$this->ignored_timestamp = NULL;
			$this->ignoredfor_days = NULL;
		}
	}

	/**
	 * Set the host operating system
	 * @param OS string
	 * @return void
	 * @access public
	 */
    public function set_os($os) {
    	$this->os = htmlspecialchars($os);
    }

	/**
	 * Set whether this machine is covered by policy
	 * ex: critical host, SPIA, etc.
	 * 
	 * @param boolean
	 * @return void
	 * @access public
	 */
    public function set_policy($covered_by_policy) {
    	$this->policy = intval($covered_by_policy);
    }

	/**
	 * Set the academic sponsor for this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Name string
     * @return void
	 */
    public function set_sponsor($name) {
    	if ($name != '')
    		$this->sponsor = htmlspecialchars($name);
    	elseif ($name == '')
    		$this->sponsor = '';
    }
    
	/**
	 * Set tags associated with this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Array of Tag ids
     * @return void
	 */
    public function set_tag_ids($array) {
    	if (is_array($array)) {
	    	//sanitize the array
	    	$array = array_map('intval', $array);
	    	$this->tag_ids = $array;
    	}
    }

	/**
	 * Set the technical sponsor for this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Name string
     * @return void
	 */
    public function set_technical($name) {
    	if ($name != '')
    		$this->technical = htmlspecialchars($name);
    	elseif ($name == '')
    		$this->technical = '';
    }

} /* end of class Host */

?>
