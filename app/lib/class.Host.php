<?php
/**
 * class.Host.php
 *
 *
 * This file is part of HECTOR.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
 
/**
 * Set up error reporting
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
require_once('class.Nmap_result.php');
require_once('class.Host_group.php');
require_once('class.Supportgroup.php');
require_once('class.Location.php');
require_once('class.Tag.php');

/**
 * Hosts are the crux of the system
 *
 * @access public
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo enable MAC address tracking
 */
class Host extends Maleable_Object implements Maleable_Object_Interface {
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
	 * The string version of the IP address
	 *
	 * @access private
	 * @var String The IP address
	 */
	private $ip = null;
	
	/**
	 * Name of the host
	 * 
	 * @access private
	 * @var String The hostname for this host
	 */
	private $name;
	
	/**
	 * Operating system of the host
	 * 
	 * @access private
	 * @var String The operating system for the host
	 */
	private $os = null;
	
	/**
	 * Ports that are detected on the host
	 *
	 * @access private
	 * @var Array An arry of Int port numbers
	 * @deprecated x.x - Sep 9, 2013
	 */
	private $ports = null;
	
	/**
	 * Host groups to which this host belongs
	 *
	 * @access private
	 * @var Array An array of Host_group ids
	 */
	private $host_group_ids = array();
	
	/**
	 * Tags applied to this host
	 *
	 * @access private
	 * @var Array An array of Tag ids associated with the host
	 */
	private $tag_ids = array();
	
	/**
	 * Alternative hostnames for the host
	 *
	 * @access private
	 * @var Array An array of (String) alternative hostnames for the host
	 */
	private $alt_hostnames = array();
	
	/**
	 * Alternative IP addresses for the host
	 *
	 * @access private
	 * @var Array An array of Strings for alternate IP addresses
	 */
	private $alt_ips = array();
	
	/**
	 * Contact for the host
	 *
	 * @access private
	 * @var String The name of the contact person for this host
	 */
	private $sponsor = "";
	
	/**
	 * Technical contact for the host
	 *
	 * @access private
	 * @var String The name of the technical contact for this host.
	 */
	private $technical = "";
	
	/**
	 * Support group object
	 * 
	 * @var Supportgroup The Supportgroup object associated with this host
	 * @access private
	 */
	private $supportgroup = "";
	
	/**
	 * Location object
	 * 
	 * @access private
	 * @var Location The Location object for this host
	 */
	private $location = "";
	
	/**
	 * Horrible, terrible, free form note field
	 * that we hope will never be used.
	 *
	 * @access private
	 * @var String Random notes associated with the host
	 */
	private $note = "";
	
	/**
	 * Is the machine governed by policy
	 * (i.e. does it contain confidential data)
	 * 
	 * @access private
	 * @var Boolean Is the machine governed by policy such as HIPPA, FERPA, etc.
	 */
	private $policy = FALSE;
	
	/**
	 * Link to an external reference on the machine
	 * 
	 * @access private
	 * @var String A URL to an external reference, ticket, or documtnation of the host.
	 */
	private $link;
	
	/**
	 * Array of host's urls and screenshot filenames (url_url, url_screenshot)
	 * 
	 * @access private
	 * @var Array An array of String => String (URL => Filename of screenshots for this host)
	 */
	private $urls = array();
	
	/**
	 * Exclude this host from future port scans?
	 *
	 * @access private
	 * @var Boolean Should the host be excluded from future vulnerability scans
	 */
	private $ignore_portscan = NULL;
	
	/**
	 * User id of the person who excluded this host
	 * from portscans
	 *
	 * @access private
	 * @var Int The unique id of the User who excluded the host from future vuln scans
	 */
	private $ignore_portscan_byuserid = NULL;
	
	/**
	 * How many days to exclude this host from future
	 * port scans
	 *
	 * @access private
	 * @var Int The number of days the host should be excluded from vuln scans
	 */
	private $ignoredfor_days = NULL;
	
	/**
	 * Time an exclusion was put into place (for 
	 * removing this host from port scans)
	 *
	 * @access private
	 * @var Int The unix timstamp of the start time for the exclusion of the host from vuln scans.
	 */
	private $ignored_timestamp = NULL;
	
	/**
	 * Note for why the host is excluded from scans.
	 *
	 * @access private
	 * @var String Any notes associated with the vuln scan exclusion.
	 */
	private $ignored_note = NULL;
	
	// --- OPERATIONS ---
	
	/**
	 * Create a new Host object
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Int The optional id of the Host from the data layer
	 * @param String 'Yes' if we want only a skeletal version of the object
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
				$this->set_id($result[0]->host_id);
				$this->set_ip($result[0]->host_ip);
				$this->set_name($result[0]->host_name);
				if ($minimal == 'no') {
					$this->set_os($result[0]->host_os);
					$this->set_sponsor($result[0]->host_sponsor);
					$this->set_link($result[0]->host_link);
					$this->set_note($result[0]->host_note);
					$this->set_supportgroup_id($result[0]->supportgroup_id);
					$this->set_location_id($result[0]->location_id);
					$this->set_technical($result[0]->host_technical);
					$this->set_policy($result[0]->host_policy);
					$this->set_ignore_portscan($result[0]->host_ignore_portscan);
					$this->set_ignore_portscan_byuserid($result[0]->host_ignoredby_user_id);
					$this->set_ignoredfor_days($result[0]->host_ignoredfor_days);
					$this->set_ignored_timestamp($result[0]->host_ignored_timestamp);
					$this->set_ignored_note($result[0]->host_ignored_note);
					$sql= array('SELECT url_id, url_url, url_screenshot ' .
							'FROM url WHERE host_id = ?i', 
							$id
							);
					$url_results = $this->db->fetch_object_array($sql);
					foreach($url_results as $url) {
						// Do some housekeeping to delete non-existent screenshots
						global $approot;
						if ( ! file_exists($approot . 'screenshots/' . $url->url_screenshot)) {
							$sql = array('UPDATE url ' .
									'SET url_screenshot = NULL ' .
									'WHERE url_id = ?i ',
									$url->url_id
									);	
							$this->db->iud_sql($sql);
						}
						$this->set_add_url($url->url_url, $url->url_screenshot);
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
						foreach ($result as $record) {
							$this->set_add_host_group_id($record->host_group_id);
						}
					}
					// Populate the alterantive names
					$sql = array(
						'SELECT host_alt_name from host_alts WHERE host_id = ?i',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						foreach ($result as $record) {
							$this->set_alt_hostname($record->host_alt_name);
						}
					}
					// Populate the alternative IP's
					$sql = array(
						'SELECT host_alt_ip from host_alts WHERE host_id = ?i',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						foreach ($result as $record) {
							$this->set_alt_ip($record->host_alt_ip);
						}
					}
					// Populate the tags
					$sql = array(
						'SELECT tag_id from host_x_tag WHERE host_id = ?i',
						$id
					);
					$result = $this->db->fetch_object_array($sql);
					if (is_array($result)) {
						$tagids = array();
						foreach ($result as $record) {
							$tagids[] = $record->tag_id;
						}
						$this->set_tag_ids($tagids);
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
	 * @param Int The unique id fo the Host_group 
	 * @return void
	 */
	public function add_host_group_id($id) {
		$id = intval($id);
		// prevent dupes
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
	 * @return Boolean Should this host be excluded from portscans?
	 */
	private function check_expire_scan_exclusion() {
		$retval = FALSE;
		if ($this->ignoredfor_days > 0) {
			$sql = 'SELECT DATEDIFF(' .
						'DATE_ADD(host_ignored_timestamp, ' .
						'INTERVAL host_ignoredfor_days DAY), NOW()) ' .
						'AS exclude FROM host WHERE host_id = ' . $this->id;
			$active_exclude = $this->db->fetch_object_array($sql);
			if (is_array($active_exclude) && $active_exclude[0]->exclude < 0) {
				// Exclusion has expired
				$this->log->write_message("Expiring portscan exclusion for host id " + $this->id);
				$this->set_portscan_exclusion(0);
				$sql = 'UPDATE host SET host_ignore_portscan = 0 ' .
						'WHERE host_id = ' . $this->id;
				$this->db->iud_sql($sql);
			}
			elseif(is_array($active_exclude)) {
				$retval = TRUE;
			}
		}
		return $retval;
	}

	/**
	 * This function is designed to avoid collisions
	 * in the data storage.  If the host is assigned
	 * the same IP as another host or the same name
	 * as another host this check will return false.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Boolean False if this host has a duplicate IP or hostname
	 */
	public function check_save() {
		$retval = TRUE;
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
					$this->log->write_error('Duplicate IP (' . $this->get_ip() . 
						') exists from host id ' . $this->get_id());
					$retval = FALSE;
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
					$retval = FALSE;
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
     * @return Boolean False if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM host WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Clear out links
	    	$sql = array(
	    		'DELETE FROM host_alts WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM url WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM host_x_host_group WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	
	    	$sql = array(
	    		'DELETE FROM nmap_result WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM host_x_tag WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM vuln_detail WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM alert WHERE host_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	
	    	// Delete from the ossec_alert table? Not sure...
	    	
	    	$this->set_id(null);
    	}
    	return $retval;
    }

	/**
	 * Render the add or edit form for the interface.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array The Array for the default CRUD template.
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
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array An array of HTML display safe String alternate hostnames
	 */
	public function get_alt_hostnames() {
		$retval = array();
		if (is_array($this->alt_hostnames)) {
			$retval = $this->alt_hostnames;
			array_map('htmlspecialchars', $retval);
		}
		return $retval;
	}

	/**
	 * Get alternative IP addresses for this host.
	 * 
	 * @access private
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array An array of String alternative IP addresses
	 */
	private function get_alt_ips() {
		return $this->alt_ips;
	}

    /**
     *  This function directly supports the Collection class.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param String The optional additional SQL WHERE clause arguments
	 * @param String The optional SQL ORDER BY clause arguments
	 * @return String SQL select string
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
	 * @param Int Port to filter resulting hosts on (say get all hosts with port 80) TCP & UDP
	 * @param String The optional SQL ORDER BY clause arguments
	 * @return String SQL select string
	 */
	public function get_collection_by_port($port, $orderby='') {
		global $appuser;
		$portnum = intval($port);
		$sql = 'select h.host_id from host h, nmap_result n ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= ', user_x_supportgroup x ';
		}
		$sql .= 'where n.host_id = h.host_id and ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= 'h.supportgroup_id = x.supportgroup_id AND' .
					'x.user_id = ' . $appuser->get_id() . ' AND ';
		}
		$sql .= 'n.nmap_result_port_number = ' . $portnum .' and n.state_id=1';
		return $sql; 
	}
	
	/**
     * This function directly supports the Collection class.
     * Gets a collection based on a service version filter.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param String Service version String to search on (ex. "OpenSSH v5.1")
	 * @param String The optional SQL ORDER BY clause arguments
	 * @return String SQL select string
	 */
	public function get_collection_by_version($version, $orderby='') {
		global $appuser;
		$portnum = strtolower(mysql_real_escape_string($version));
		$sql = 'select h.host_id from host h, nmap_result n ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= ', user_x_supportgroup x ';
		}
		$sql .= 'where n.host_id = h.host_id and ';
		if (isset($appuser) && ! $appuser->get_is_admin()) {
			$sql .= 'h.supportgroup_id = x.supportgroup_id AND' .
					'x.user_id = ' . $appuser->get_id() . ' AND ';
		}
		$sql .= 'LOWER(n.nmap_result_service_version) LIKE \'%' . $version . '%\'';
		return $sql; 
	}

	/**
	 * The method to return the HTML for the details on this specific host.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return HTML chunk
	 * @deprecated 2013.08.28 - Aug 28, 2013
	 */
	public function get_details() {
		// This function is deprecated
		return true;
	}

	/**
	 * Get information for display via a template.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array An array for the default display template
	 */
	public function get_displays() {
		return array('Name'=>'get_name_linked', 
				'IP'=>'get_ip', 
				'OS'=>'get_os',  
				'Host groups'=>'get_host_groups_readable');
	}

	/**
	 * Return the User object for the user who excluded this
	 * host from port scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int The id of the User who excluded the host
	 */
	public function get_excludedby() {
		global $appuser;
		return (int) $this->ignore_portscan_byuserid;
	}

	/**
	 * Return the username for the user who excluded this
	 * host from port scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The name of the User who excluded the host
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
	 * @return Timestamp The timestamp of the host's exclusion
	 */
	public function get_excludedon() {
		return (int) $this->ignored_timestamp;
	}

	/**
	 * Return the number of days this host was excluded from scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int The number of days to exclude the host from scans
	 */
	public function get_excludedfor() {
		return (int) $this->ignoredfor_days;
	}

	/**
	 * Report the reason this host was excluded from scans.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The HTML display safe reason for the exclusion
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
	 * @return Array An array of integers for Host_groups for this host
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
	 * @return String A list of Host_group names for this host
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
	 * Should this host be exempted from portscans?
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Boolean True if this host should be exempt, False otherwise
	 */
	public function get_ignore_portscan() {
		return (bool) $this->ignore_portscan;
	}
	/**
	 * Get the host's IP address
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The string representation of this host's IP address
	 */
	public function get_ip() {
		return $this->ip;
	}
	
	/**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Host';
    } 
    
	/**
	 * Get any link to external resources about this Host
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String A URL to external resources about this Host
	 */
	public function get_link() {
		return htmlspecialchars($this->link);
	}
	/**
	 * Return the location_id for the Location of the host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int The unique id for the Location of this Host or null
	 */
	public function get_location_id() {
		return (is_object($this->location)) ? $this->location->get_id() : null;
	}
	
	/**
	 * Return the name for the Location of the host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The name of the Location for this Host, or null
	 */
	public function get_location_name() {
		return (is_object($this->location)) ? $this->location->get_name() : null;
	}
	
	/**
	 * Return the hostname for this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The owercase HTML safe display name for the host
	 */
	public function get_name() {
		$retval = null;
		if ($this->name != null) $retval = $this->name;
		else if ($this->ip != null) {
			$retval = gethostbyaddr($this->ip);
			$this->set_name($retval);
		}
		return htmlspecialchars(strtolower($retval));
	}
	
	/**
	 * Return the hostname as a hypterlinked chunk of 
	 * HTML to insert into display so that the name can
	 * be clicked to view details.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String An HTML chunk linking the host to details in HECTOR
	 */
	public function get_name_linked() {
		$retval = null;
		if ($this->ip != null) {
			$name = gethostbyaddr($this->ip);
			$this->set_name($name);
		}
		$retval = '<a href="?action=host_details&id=' .
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
	 * @return String HTML safe disply for any notes associated with the Host
	 */
	public function get_note() {
		return htmlspecialchars($this->note);
	}
	
	/**
	 * Should this host be excluded from port scans?
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Boolean True if it should be excluded.
	 */
	public function get_portscan_exclusion() {
		return (boolean) $this->ignore_portscan;
	}
	
	/**
	 * Get the number of open ports.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int The number of open (TCP & UDP) ports on the host
	 * @todo Update this method for multiple records over dates
	 */
	public function get_open_ports() {
		$retval = 0;
		$sql = array('SELECT COUNT(nmap_result_id) AS portcount ' .
				'FROM nmap_result ' .
				'WHERE host_id = ?i ' .
				'AND state_id = 1',
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
	 * @return String The HTML display safe operating system
	 */
	public function get_os() {
		return htmlspecialchars($this->os);
	}
	/**
	 * Is this host covered by policy?  Meaning does this
	 * host have sensitive data.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Boolean True if this Host is covered by policy
	 */
	public function get_policy() {
		return (bool) $this->policy;
	}
	/**
	 * Get a collection of ports as nmap_result 
	 * objects using the Collection factory.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array An array of open ports on this host
	 */
	public function get_ports() {
		if ($this->ports == null) {
			$orderby = 'and nsr.host_id = ' . $this->get_id() . 
				' order by nsr.nmap_result_port_number asc';
			$ports = new Collection('Nmap_result', $orderby);
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
	 * @return String The HTML safe display of the sponsor for this Host
	 */
	public function get_sponsor() {
		return htmlspecialchars($this->sponsor);
	}
	
	/**
	 * ID for the Supportgroup to which this
	 * host is assigned.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Int The id of the Supportgroup associated with this record or null
	 */
	public function get_supportgroup_id() {
		return (is_object($this->supportgroup)) ? $this->supportgroup->get_id() : null;
	}
	
	/**
	 * Name for the Supportgroup to which this
	 * host is assigned.
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Strin The name of the Supportgroup or null
	 */
	public function get_supportgroup_name() {
		return (is_object($this->supportgroup)) ? $this->supportgroup->get_name() : null;
	}
	
	/**
	 * The technical contact for this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The HTML display safe name of the technical contact for the Host
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
	 * @return Boolean False if anything goes awry
	 */
	public function process_form($attribute, $value) {
		$retval = FALSE;
		$this->$attribute($value);
		$retval = $this->save();
		return $retval;
	}
	
	/**
	 * Get a list of id's for tags associated with this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array of integers
	 */
	public function get_tag_ids() {
		return array_map('intval', $this->tag_ids);
	}
	
	/**
	 * Get a list of names
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Array An Array of HTML display safe String Tag names for this Host
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
	 * @return Array An array of String => String (URL => Filename of screenshots for this host)
	 */
	public function get_urls() {
		return $this->urls;
	}
	
	/**
	 * Populate this object from data that may
	 * already exist.
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 */
	public function lookup_by_ip() {
		if (! isset($this->ip)) return;
		$sql = array(
					'SELECT host_id FROM host WHERE host_ip = \'?s\'',
					$this->ip
				);
		$result = $this->db->fetch_object_array($sql);
		if (is_array($result) && is_object($result[0])) {			
			$this->__construct($result[0]->host_id);
		}			
	}

	/**
	 * Remove this host form specificed host group
	 * 
	 * @param Int The Host_group id to remove from association with the Host
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
	 * @return Boolean False if anything goes awry
	 */
	public function save() {
		$retval = FALSE;
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
	    			'host_policy = ?b, ' .
	    			'host_link = \'?s\', ' .
	    			'host_ignore_portscan = ?b, ' .
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
	    	$retval = $this->db->iud_sql($sql);
		}
		else {
			// Insert a new value
	    	$sql = array(
	    		'INSERT INTO host SET host_ip = \'?s\', ' .
	    		  'host_ip_numeric = inet_aton(host_ip), ' .
	    			'host_ignore_portscan = \'?i\', ' .
	    			'host_ignoredby_user_id = \'?i\', ' .
	    			'host_ignoredfor_days = \'?i\', ' .
	    			'host_ignored_timestamp = now(), ' .
	    			'host_ignored_note = \'?s\', ' .
	    			'host_link = \'?s\', ' .
	    			'host_name = \'?s\', ' .
	    			'host_note = \'?s\', ' .
	    			'host_policy = ?b, ' .
	    			'host_os = \'?s\', ' .
	    			'host_sponsor = \'?s\', ' .
	    			'host_technical = \'?s\', ' .
	    			'location_id = \'?i\', ' .
	    			'supportgroup_id = \'?i\' ',
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
	    	$retval = $this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->last_id > 0) {
	    		$this->set_id($result[0]->last_id);
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
		return $retval;
	}
	
	/**
	 * Add a new Host_group id to the attribue Array
	 * 
	 * @access public
	 * @param Int The unique id of the Host_group
	 * @return Boolean False if the id isn't above zero or an int
	 */
	public function set_add_host_group_id ($id) {
		$retval = FALSE;
		$id = intval($id);
		if ($id > 0) {
			if (! in_array($id, $this->host_group_ids)) {
				$this->host_group_ids[] = $id;
			}
			$retval = TRUE;
		}
		return $retval;
	}
	
	/**
	 * Add a new URL/filename for this host
	 * 
	 * @access public
	 * @param String A valid URL for the screenshot's subject
	 * @param String A valid filename of the screenshot image
	 * @return Boolean False if the URL doesn't validate or the screenshot doesn't exist.
	 */
	public function set_add_url($url, $filepath) {
		global $approot;
		$retval = FALSE;
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			$retval = TRUE;
		}
		$filepath = $approot . 'screenshots/' . $filepath;
		if (! file_exists($filepath)) $retval = FALSE;
		if ($retval) {
			// prevent dupes
			if (! in_array($url, $this->urls)) {
				$this->urls[] = array($url, $filepath);
			}
		}
		return $retval;
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
    	$this->alt_hostnames[] = $name;
    }

	/**
	 * Set the alternate host ip
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param String Valid IP address
	 * @return Boolean False if IP doesn't validate
	 */
    public function set_alt_ip($ip) {
    	$retval = FALSE;
    	if (filter_var($ip, FILTER_VALIDATE_IP)) {
    		$this->alt_ips[] = $ip;
    		$retval = TRUE;
    	}
    	return $retval;
    }
    /**
	 * Setter for the exclusion period
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Int Number of days to exclude the host from vuln scans
	 * @return Boolean False if param isn't a positive int
	 */
	public function set_excludedfor($days) {
		$retval = FALSE;
		if (intval($days) > 0) {
			$this->ignoredfor_days = intval($days);
			$retval = TRUE;
		}
		return $retval;
	}
	
    /**
	 * Setter for the exclusion notes
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param String The exclusion notes for the Host
	 * @return void
	 */
	public function set_excludedreason($note) {
		$this->ignored_note = $note;
	}
	
    /**
	 * Setter for the user id who excluded this host
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Int The ID of the user who excluded the Host from vuln scans
	 * @return void
	 */
	public function set_excludedby($id) {
		$this->ignored_portscan_byuserid = intval($id);
	}

	/**
	 * Set the host groups for this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Array An Array of Host_group ids
     * @return Boolean False if the input isn't an array
	 */
    public function set_host_group_ids($array) {
    	$retval = FALSE;
    	if (is_array($array)) {
	    	//sanitize the array
	    	$array = array_map('intval', $array);
	    	$this->host_group_ids = $array;
    	}
    	return $retval;
    }
    
    /**
     * Alias for set_portscan_exclusion
     * 
     * @access public
     * @param Boolean Whether the Host should be excluded from vuln scans
     * @return void;
     */
    public function set_ignore_portscan($bool) {
    	$this->set_portscan_exclusion($bool);
    }
    
    /**
     * Who is issuing the exclusion
     * 
     * @access public
     * @param Int The User id of the user who is excluding the host from vuln scans
     * @return void;
     */
    public function set_ignore_portscan_byuserid($id) {
    	$this->ignore_portscan_byuserid = intval($id);
    }     
    
    /**
     * Notes about the exclusion
     * 
     * @access public
     * @param String Notes about the exclusion
     * @return void;
     */
    public function set_ignored_note($note) {
    	$this->ignored_notes = $note;
    }
    
    /**
     * When did we ignore the host
     * 
     * @access public
     * @param Int Timestamp that we started ignoring the host
     * @return void;
     */
    public function set_ignored_timestamp($tstamp) {
    	$this->ignored_timestamp = intval($tstamp);
    }
    
    /**
     * How long do we ignore the host
     * 
     * @access public
     * @param Int Number of days to ignore the Host
     * @return void;
     */
    public function set_ignoredfor_days($days) {
    	$this->ignoredfor_days = intval($days);
    }

	/**
	 * Set the IP for this host
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  ip
	 * @return Boolean False if the IP doesn't validate
	 */
	public function set_ip($ip) {
		$retval = FALSE;
		if ($ip = filter_var($ip, FILTER_VALIDATE_IP)) {
			$this->ip = $ip;
			$retval = TRUE;
		}
		return $retval;
	}
		
	/**
	 * Set the link to external documentation of this host.
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param  String $link
	 * @return Boolean False if the link doesn't validate
	 */
	public function set_link($link) {
		$retval = FALSE;
		if ($link = filter_var($link, FILTER_VALIDATE_URL)) {
			$this->link = $link;
			$retval = TRUE;
		}
		return $retval;
	}
	
	/**
	 * Set the location id
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Int The unique id of the Location
	 * @return Boolean False if the input isn't a positive integer of Location doens't exist
	 */
	public function set_location_id($id) {
		$retval = FALSE;
    	$id = intval($id);
    	if ($id > 0) {
    		$this->location = new Location($id);
    		if ($this->location->get_id() > 0) {
    			$retval = TRUE;
    		}
    	}
		return $retval;
	}

	/**
	 * Set the Support group id
	 * 
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param Int The Supoprtgroup id
	 * @return Boolean if the Supportgroup id isn't valid
	 */
    public function set_supportgroup_id($id) {
    	$retval = FALSE;
    	$id = intval($id[0]);
    	if ($id > 0) {
    		$this->supportgroup = new Supportgroup($id);
    		if ($this->supportgroup->get_id() > 0) {
    			$retval = TRUE;
    		}
    	}
		return $retval;
    }

	/**
	 * Set the host name
	 * 
	 * @param String The name of the host
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access public
	 */
    public function set_name($name) {
    	$this->name = $name;
    }

	/**
	 * Set the host note
	 * 
	 * @param String Any notes associated with this host
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access public
	 */
    public function set_note($note) {
    	$this->note = $note;
    }
    
    /**
	 * Setter to exclude this host from portscans.
	 * 
	 * @param Boolean Whether the Host should be excluded from vuln scans
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return void
	 * @access public
	 */
	public function set_portscan_exclusion($val) {
		$this->ignore_portscan = ((bool) $val) ? TRUE : FALSE;
		if (isset($appuser)) $this->set_excludedby($appuser->get_id());
		if ((bool) $val === FALSE) {
			// Unsetting the ignore so clear associated data
			$this->ignore_portscan_byuserid = NULL;
			$this->ignored_noe = NULL;
			$this->ignored_timestamp = NULL;
			$this->ignoredfor_days = NULL;
		}
	}

	/**
	 * Set the host operating system
	 * 
	 * @param String The operating system for the host.
	 * @return void
	 * @access public
	 */
    public function set_os($os) {
    	$this->os = $os;
    }

	/**
	 * Set whether this machine is covered by policy
	 * ex: critical host, SPIA, etc.
	 * 
	 * @param Boolean Whether or not the Host is covered by policy
	 * @return void
	 * @access public
	 */
    public function set_policy($covered_by_policy) {
    	$this->policy = (bool) $covered_by_policy;
    }

	/**
	 * Set the sponsor for this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  String The name of the sponsor for the Host
     * @return void
	 */
    public function set_sponsor($name) {
    	$this->sponsor = $name;
    }
    
	/**
	 * Set tags associated with this host
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Array An Array of Tag ids (integers)
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
     * @param  String The name of the technical support person
     * @return void
	 */
    public function set_technical($name) {
    	$this->technical = $name;
    }
    
    /**
	 * Add a new URL/filename for this host
	 * 
	 * @access public
	 * @param Array An array of [url, filename] for each screenshot
	 * @return Boolean False if any URL doesn't validate or screenshot doesn't exist.
	 */
	public function set_urls($urls) {
		global $approot;
		$retval = FALSE;
		if (is_array($urls)) {
			$this->urls = array();
			if (count($urls) == 0) { // reset array
				$retval = TRUE;
			}
			else {
				foreach ($urls as $urllist) {
					$url = $urllist[0];
					$fname = $urllist[1];
					$filepath = $approot . 'screenshots/' .$fname;
					if (! file_exists($filepath)) $retval = FALSE;
					else {
						if (! filter_var($url, FILTER_VALIDATE_URL)) {
							$retval = FALSE;
						}
						else {
							// prevent dupes
							if (! in_array($url, $this->urls)) {
								$this->urls[] = array($url, $fname);
							}
							$retval = TRUE;
						}
					}
					if (! $retval) {
						return FALSE;
					}
				}
			}
		}
		return $retval;
	}

} /* end of class Host */

?>
