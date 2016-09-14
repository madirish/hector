<?php 
/**
 * HECTOR - class.Ossec_Alert.php
 * 
 * @author Ubani A Balogun <ubani@sas.upenn.edu>
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR 
 * 
 */

/**
 *  Error reporting
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
 * The Ossec_Alert class handles interactions with the HECTOR ossec_alert table
 * Ossec Alerts are alerts received from the Hector Ossec install
 * 
 * @package HECTOR
 * @author Ubani A Balogun <ubani@sas.upenn.edu>
 * @author Justin C. Klein Keane <justin@madirish.net>
 */

class Ossec_Alert extends Maleable_Object {
	// --- Attributes ---
	
	/**
	 * Timestamp of the Alert
	 * 
	 * @access private
	 * @var timestamp of the ossec alert
	 */
	private $alert_date;
	
	/**
	 * The log filename from which the OSSEC alert was generated (syslog, maillog, etc.)
	 * 
	 * @access private
	 * @var String The log filename from which the OSSEC alert was generated (syslog, maillog, etc.)
	 */
	private $alert_log;
	
	/**
	 * The OSSEC alert id value from the data layer, assigned by OSSEC (ex 1473739283.14752)
	 * 
	 * @access private
	 * @var String The OSSEC alert id value from the data layer, assigned by OSSEC (ex 1473739283.14752)
	 */
	private $alert_ossec_id;
	
	/**
	 *  Instance of the Db
	 *
	 *  @access private
	 *  @var Db An instance of the Db
	 */
	private $db = null;
	
	/**
	 * The host id for the ossec_alert
	 * 
	 * @access private
	 * @var Int the host id related to the ossec alert
	 */
	private $host_id;
	
	/**
	 * Unique id from the data layer
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
	 * The id of the associated Ossec_Rule
	 * 
	 * @access private 
	 * @var Int The id of the associated Ossec_Rule
	 */
	private $rule_id;
	
	/**
	 * The ipv4 rule destination ip
	 * 
	 * @access private
	 * @var String the rule destination ip for the ossec alert
	 */
	private $rule_dst_ip;
	
	/**
	 * The numeric representation of the rule destination ip
	 * 
	 * @access private
	 * @var Int numeric representation of the rule destination ip
	 */
	private $rule_dst_ip_numeric;
	
	/**
	 * The ipv4 rule source ip
	 * 
	 * @access private
	 * @var String the rule source ip for the ossec alert
	 */
	private $rule_src_ip;
	
	/**
	 * The numeric representation of the rule source ip
	 * 
	 * @access private
	 * @var Int numeric representation of the rule source ip
	 */
	private $rule_src_ip_numeric;
	
	/**
	 * The rule_user value from the data layer
	 * 
	 * @access private
	 * @var String the rule_user value from the data layer
	 */
	private $rule_user;
	
	/**
	 * The actual log message from the host that generated the alert (i.e. "Authentication failed")
	 * 
	 * @access private
	 * @var String The actual log message from the host that generated the alert (i.e. "Authentication failed")
	 */
	private $rule_log;
	
	
	// -- Operations --
	
	/**
	 * Contruct a new Ossec Alert or instanstiate one from the data layer by ID
	 * 
	 * @access public 
	 * @param Int the unique ID of the OSSEC ALERT
	 * @return void
	 */
	public function __construct($id='', $alert_id=''){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id !=''){
			$sql = array(
				'SELECT alert_id as ossec_alert_id, alert_date, host_id, 
					alert_log, rule_id, rule_src_ip, rule_src_ip_numeric, 
					rule_user, rule_log, alert_ossec_id, rule_dst_ip,
					rule_dst_ip_numeric 
					FROM ossec_alert o WHERE o.alert_id = ?i',
					$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])){
				$r = $result[0];
				$this->set_id($r->ossec_alert_id);
				$this->set_alert_date($r->alert_date);
				$this->set_host_id($r->host_id);
				$this->set_alert_log($r->alert_log);
				$this->set_rule_id($r->rule_id);
				$this->set_rule_dst_ip($r->rule_dst_ip);
				$this->set_rule_dst_ip_numeric($r->rule_dst_ip_numeric);
				$this->set_rule_src_ip($r->rule_src_ip);
				$this->set_rule_src_ip_numeric($r->rule_src_ip_numeric);
				$this->set_rule_user($r->rule_user);
				$this->set_rule_log($r->rule_log);
				$this->set_alert_ossec_id($r->alert_ossec_id);
				
			}
		}
		if ($alert_id !=''){
			$sql = array(
				'SELECT alert_id as ossec_alert_id, alert_date, host_id, 
					alert_log, rule_id, rule_src_ip, rule_src_ip_numeric, 
					rule_user, rule_log, alert_ossec_id, rule_dst_ip,
					rule_dst_ip_numeric 
					FROM ossec_alert o WHERE o.alert_ossec_id = ?i',
					$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])){
				$r = $result[0];
				$this->set_id($r->ossec_alert_id);
				$this->set_alert_date($r->alert_date);
				$this->set_host_id($r->host_id);
				$this->set_alert_log($r->alert_log);
				$this->set_rule_id($r->rule_id);
				$this->set_rule_dst_ip_numeric($r->rule_dst_ip_numeric);
				$this->set_rule_src_ip($r->rule_src_ip);
				$this->set_rule_src_ip($r->rule_src_ip);
				$this->set_rule_src_ip_numeric($r->rule_src_ip_numeric);
				$this->set_rule_user($r->rule_user);
				$this->set_rule_log($r->rule_log);
				$this->set_alert_ossec_id($r->alert_ossec_id);
				
			}
		}
	}
	/**
	 * Get the timestamp of the ossec alert
	 *
	 * @access public
	 * @return Timestamp The timestamp of the ossec alert
	 */
	public function get_alert_date(){
		return $this->alert_date;
	}
	
	/**
	 * Get the alert log for the ossec alert
	 * 
	 * @access public
	 * @return String the html safe log filename from which the OSSEC alert was generated (syslog, maillog, etc.)
	 */
	public function get_alert_log(){
		return htmlspecialchars($this->alert_log);
	}
	
	/**
	 * Get the alert ossec id
	 * 
	 * @access public
	 * @return String the html safe alert ossec id
	 */
	public function get_alert_ossec_id(){
		return htmlspecialchars($this->alert_ossec_id);
	}
	
	/**
	 *  This function directly supports the Collection class.
	 *
	 *  @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = ''){
		$sql = 'SELECT o.alert_id as ossec_alert_id FROM ossec_alert o, ossec_rule r WHERE o.alert_id > 0 and o.rule_id = r.rule_id and r.rule_level > 7';
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
			$sql .= ' ORDER BY o.alert_date desc LIMIT 1000';
		}
		return $sql;
	}
	
	/**
	 * Get the host_id of the ossec alert 
	 * 
	 * @access public
	 * @return Int the host id for the ossec alert
	 */
	public function get_host_id(){
		return $this->host_id;
	}
	
	/**
	 *  Get the unique ID for the object
	 *
	 *  @access public
	 *  @return Int The unique ID of the object
	 */
	public function get_id(){
		return intval($this->id);
	}
	
	/**
	 * This function returns the attributes of the object in a associative array
	 * 
	 * @return Array an associative array of the objects attributes
	 */
	public function get_object_as_array(){
		return array(
				'id' => $this->get_id(),
				'alert_date' => $this->get_alert_date(),
				'host_id' => $this->get_host_id(),
				'alert_log' => $this->get_alert_log(),
				'rule_id' => $this->get_rule_id(),
				'rule_src_ip' => $this->get_rule_src_ip(),
				'rule_src_ip_numeric' => $this->get_rule_src_ip_numeric(),
				'rule_user' => $this->get_rule_user(),
				'rule_log' => $this->get_rule_log(),
				'alert_ossec_id' => $this->get_alert_ossec_id(),
				'rule_level' => $this->get_ossec_rule_level(),
				'rule_message' => $this->get_ossec_rule_message(),
		);
	}
	
	
	/**
	 *  Get ossec alerts in the last week
	 *  
	 *  @access public
	 *  @return String the sql for the collection definition
	 */
	public function get_ossec_alerts_in_last_week(){
		$ossec_alert_filter = " AND alert_date >= DATE_SUB(NOW(), INTERVAL 5 DAY) ";
		return $this->get_collection_definition($ossec_alert_filter);
	}
	
	/**
	 * The OSSEC assigned ID (in format 123456.78)
	 * 
	 * @access public
	 * @return String The OSSEC assigned alert id (in format 1234.56)
	 */
	public function get_ossec_id() {
		return $this->alert_ossec_id;
	}
	
	/**
	 * Get the ossec rule object for the ossec alert
	 * 
	 * @access public 
	 * @return Ossec_Rule the ossec rule for the alert
	 */
	public function get_ossec_rule(){
		include_once('class.Ossec_Rule.php');
		$rule = new Ossec_Rule($this->get_rule_id());
		return $rule; 
	}
	
	
	/**
	 * Get the rule level for the ossec alert
	 * 
	 * @access public
	 * @return Int the rule level for the ossec alert
	 */
	public function get_ossec_rule_level(){
		$rule = $this->get_ossec_rule();
		return $rule->get_rule_level();
	}
	
	/**
	 * Get the ossec rule message
	 * 
	 * @access public
	 * @return String the ossec rule message
	 */
	public function get_ossec_rule_message(){
		$rule = $this->get_ossec_rule();
		return $rule->get_rule_message();
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
		$sql = 'SELECT ?s, count(?s) as frequency FROM ossec_alert WHERE id > 0 ';
		if ($bound != ''){
			$sql .= ' AND alert_date > DATE_SUB(NOW(), INTERVAL ?i DAY)';
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
	 * Get the rule destination ip for the ossec alert
	 * 
	 * @access public
	 * @return String the html safe ipv4 rule destination ip address
	 */
	public function get_rule_dst_ip(){
		return htmlspecialchars($this->rule_dst_ip);
	}
	
	/**
	 * Get the decimal representation of the rule destination ip
	 * 
	 * @access public
	 * @return Int the decimal representation of the rule destination ip
	 */
	public function get_rule_dst_ip_numeric(){
		if (isset($this->rule_dst_ip_numeric))
			return $this->rule_dst_ip_numeric;
		else 
			return ip2long($this->rule_dst_ip);
	}
	
	/**
	 * Get the rule id for the ossec alert
	 * 
	 * @access public
	 * @return Int the rule id for the ossec alert
	 */
	public function get_rule_id(){
		return $this->rule_id;
	}
	
	/**
	 * Get the actual log message from the host that generated the alert (i.e. "Authentication failed")
	 * 
	 * @access public
	 * @return String The html safe rule log entry data that generated the alert
	 */
	public function get_rule_log(){
		return htmlspecialchars($this->rule_log);
	}
	
	/**
	 * Get the rule source ip for the ossec alert
	 * 
	 * @access public
	 * @return String the html safe ipv4 rule source ip address
	 */
	public function get_rule_src_ip(){
		return htmlspecialchars($this->rule_src_ip);
	}
	
	/**
	 * Get the decimal representation of the rule source ip
	 * 
	 * @access public
	 * @return Int the decimal representation of the rule source ip
	 */
	public function get_rule_src_ip_numeric(){
		if (isset($this->rule_src_ip_numeric))
			return $this->rule_src_ip_numeric;
		else 
			return ip2long($this->rule_src_ip);
	}
	
	/**
	 * Get the rule user
	 * 
	 * @access public
	 * @return String the html safe rule user
	 */
	public function get_rule_user(){
		return htmlspecialchars($this->rule_user);
	}
	
	/**
	 * Process a line from an OSSEC alert log.
	 * 
	 * @access public
	 * @param String The line from the OSSEC log file to examine
	 */
	public function process_log_line($line) {
		/**
		 * ** Alert 1463651767.22664: mail  - syslog,errors,
		 * 2016 May 19 05:56:07 hector->/var/log/messages
		 * Rule: 1002 (level 2) -> 'Unknown problem somewhere in the system.'
		 * May 19 05:56:06 hector HECTOR[5431]: 2016-05-19 05:56:06  ERROR: 127.0.0.1  IP failed to validate at Darknet::set_dst_ip()#011
		 */
		//Process the first line
		if (substr($line, 0, 8) == '** Alert') {
			$this->process_log_alert_line($line);
		}
		// Process the source line
		elseif (preg_match('/^\d{4} [A-Z][a-z]{2} \d\d \d\d:\d\d:\d\d /', $line, $matches)) {
			$this->process_log_source_line($line, $matches);
		}
		// Process the rule line
		elseif (substr($line, 0, 6) == 'Rule: ') {
			$this->process_log_rule_line($line);
		}
		// Process source ip line
		elseif (substr($line, 0, 7) == 'Src IP:') {
			$this->process_log_src_ip_line($line);
		}
		// Process destination ip line
		elseif (substr($line, 0, 7) == 'Dst IP:') {
			$this->process_log_dst_ip_line($line);
		}
		// Process user line
		elseif (substr($line, 0, 5) == 'User:') {
			$this->process_log_user($line);
		}
		// Process the actual log entry
		else {
			$this->process_data($line);	
		}	
		
	}
	
	/**
	 * Process the line of actual log data received from the host
	 * 
	 * @access private
	 * @param String The log entry from the host
	 */
	private function process_data($line) {
		$this->set_append_rule_log($line);
	}
	
	/**
	 * Process the line of OSSEC log that contains the alert level:
	 * ** Alert 1463651767.22664: mail  - syslog,errors,
	 * 
	 * @access private
	 * @return Boolean
	 * @param String The log entry from the host
	 */
	private function process_log_alert_line($line) {
			//Find alert id start and end
			preg_match('/Alert \d+\.\d+/', $line, $matches);
			if (isset($matches[0])) {
				$this->set_alert_ossec_id(substr($matches[0], 6));
			}
			else {
				$this->log->write_error("Unable to parse out OSSEC assigned alert ID number in Ossec_Alert::process_log_alert_line");
				return false;
			}
			return true;
	}
	
	/**
	 * Process the line of OSSEC log that contains the destination IP
	 * Dst IP: 10.124.236.94
	 * 
	 * @access private
	 * @return Boolean
	 * @param String The OSSEC identified IPv4 destination IP address
	 */
	private function process_log_dst_ip_line($line) {
		preg_match('/^Dst IP: \d+.\d+.\d+\d+/',$line,$dstips);
		$dstip = isset($dstips[0]) ? substr($dstips[0], 8) : '';
		if ($dstip == '') {
			$this->log->write_error("Unable to parse destination IP in Ossec_Alert::process_log_dst_ip_line");
			return false;
		}
		$this->set_rule_dst_ip($dstip);
		return true;
	}
	
	/**
	 * Process the line of OSSEC log that contains the rule information
	 * and create a new rule if necessary.
	 * Rule: 100100 (level 2) -> 'Suppress long syslog messages for foo.mlhs.org'
	 * 
	 * @access private
	 * @return Boolean
	 * @param String The OSSEC log line that contains the rule number, level, and description.
	 */
	private function process_log_rule_line($line) {
		preg_match('/^Rule: \d+ /', $line, $matches);
		if (isset($matches[0])) {
			$rule_id_number = trim(substr($matches[0], 6));
			require_once 'class.Ossec_Rule.php';
			$rule = new Ossec_Rule('',$rule_id_number);
			if (! $rule->get_id() > 0) {
				// new rule we need to populate
				preg_match('/level \d+\)/', $line, $rule_level);
				if (isset($rule_level[0]))
					$rule->set_rule_level(substr($rule_level[0], 6));
				else 
					$this->log->write_error("Unable to parse out OSSEC rule level in Ossec_Alert::process_log_rule_line");
				preg_match("/-> '.+'/", $line, $rule_text);
				if (isset($rule_text[0])) 
					$rule->set_rule_message(substr($rule_text[0], 3));
				else 
					$this->log->write_error("Unable to find rule message text in Ossec_Alert::process_log_rule_line");
				$rule->save();
			}
			else {
				$this->set_rule_id($rule->get_id());
			}
		}
		else {
			$this->log->write_error("Unable to OSSEC rule number in Ossec_Alert::process_log_rule_line");
			return false;
		}
		return true;
	}
	
	/**
	 * Process the line of OSSEC log that contains the source IP
	 * Src IP: 10.124.236.94
	 * 
	 * @access private
	 * @return Boolean
	 * @param String The OSSEC log line identifying the IPv4 source IP address
	 */
	private function process_log_src_ip_line($line) {
		preg_match('/^Src IP: \d+.\d+.\d+\d+/',$line,$srcips);
		$srcip = isset($srcips[0]) ? substr($srcips[0], 8) : '';
		if ($srcip == '') {
			$this->log->write_error("No source IP found in Ossec_Alert::process_log_src_ip_line");
			return false;
		}
		$this->set_rule_src_ip($srcip);
		return true;
	}
	
	/**
	 * Process the line of OSSEC log that contains the source of the log and date
	 * 2016 Sep 14 00:23:23 (foo.mlhs.org) 10.103.24.50->/var/log/messages
	 * 
	 * @access private
	 * @return Boolean
	 * @param String The OSSEC log line containing the date, source host, and source log for the alert
	 */
	private function process_log_source_line($line) {
		if (isset($matches[0])) {
			$alert_date = $matches[0];
			$year = substr($alert_date, 0, 4);
			$month = substr($alert_date, 5, 3);
			$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$month = array_search($month, $months) + 1;
			if (strlen($month) < 2) $month = '0' . $month;
			$day = substr($alert_date, 9, 2);
			$time = substr($alert_date, -9);
			preg_match('/ (\w|\.)+->/', $line, $sources);
			$source = isset($sources[0]) ? substr($sources[0], 1, -2) : 'Source not found';
			preg_match('/->\S+$/', $line, $logs);
			$log = substr($logs[0], 2);
			$this->set_alert_date("$year-$month-$day $time");
			$this->set_alert_log($log);
			$this->set_alert_host_by_name($source);
		}
		else {
			return false;
		}
		return true;
	}
	
	/**
	 * Process the line of OSSEC log that contains any identified user
	 * User: root
	 * 
	 * @access private
	 * @return Boolean
	 * @param String The OSSEC log line containing user information
	 */
	private function process_log_user($line) {
		preg_match('/^User: .*/',$line,$usernames);
		$username = isset($usernames[0]) ? substr($usernames[0], 6) : '';
		if ($username == '') return false;
		$this->set_rule_user($username);
		return true;
	}
	
	/**
	 * Persist the OSSEC alert to the data layer, either creating a new record
	 * or updating an existing record
	 * 
	 * @access public
	 * @return Boolean
	 */
	public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing rule
	    	$sql = array(
	    		'UPDATE ossec_alert SET 
	    			alert_date = \'?d\',
	    			host_id = \'?i\',
	    			alert_log = \'?s\' ,
	    			rule_id = \'?i\',
	    			rule_src_ip = \'?s\',
	    			rule_src_ip_numeric = \'?i\',
	    			rule_dst_ip = \'?s\,
	    			rule_dst_ip_numeric = \'?i\',
	    			rule_user = \'?s\',
	    			rule_log = \'?s\',
	    			alert_ossec_id = \'?s\'
	    		WHERE alert_id = \'?i\'',
	    		$this->get_alert_date(),
	    		$this->get_host_id(),
	    		$this->get_alert_log(),
	    		$this->get_rule_id(),
	    		$this->get_rule_src_ip(),
	    		$this->get_rule_src_ip_numeric(),
	    		$this->get_rule_dst_ip(),
	    		$this->get_rule_dst_ip_numeric(),
	    		$this->get_rule_user(),
	    		$this->get_rule_log(),
	    		$this->get_ossec_id(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO ossec_alert SET 
	    			alert_date = \'?d\',
	    			host_id = \'?i\',
	    			alert_log = \'?s\' ,
	    			rule_id = \'?i\',
	    			rule_src_ip = \'?s\',
	    			rule_src_ip_numeric = \'?i\',
	    			rule_dst_ip = \'?s\',
	    			rule_dst_ip_numeric = \'?i\',
	    			rule_user = \'?s\',
	    			rule_log = \'?s\',
	    			alert_ossec_id = \'?s\'',
	    		$this->get_alert_date(),
	    		$this->get_host_id(),
	    		$this->get_alert_log(),
	    		$this->get_rule_id(),
	    		$this->get_rule_src_ip(),
	    		$this->get_rule_src_ip_numeric(),
	    		$this->get_rule_dst_ip(),
	    		$this->get_rule_dst_ip_numeric(),
	    		$this->get_rule_user(),
	    		$this->get_rule_log(),
	    		$this->get_ossec_id()
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
	 * Set the alert_date attribute.
	 *
	 * @access public
	 * @param Datetime The timestamp of the login attempt
	 */
	public function set_alert_date($datetime){
		$this->alert_date = date("Y-m-d H:i:s", strtotime($datetime));
	}
	
	/**
	 * Set up the Host values for the alert, looking them up by hostname
	 * 
	 * @access public
	 * @return Boolean
	 * @param String The hostname to use for the lookup of the Host
	 */
	public function set_alert_host_by_name($hostname) {
		require_once 'class.Host.php';
		$host = new Host();
		$host->lookup_by_name($hostname);
		if (! $host->get_id() > 0) {
			$host->set_name($hostname);
			$host->set_ip(gethostbyname($hostname));
			$host->save();
		}
		if ($this->set_host_id($host->get_id())) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Set the alert_log attribute
	 * 
	 * @access public
	 * @param String The log filename from which the OSSEC alert was generated (syslog, maillog, etc.)
	 */
	public function set_alert_log($alert_log){
		$this->alert_log = $alert_log;
	}
	
	/**
	 * Set the alert_ossec_id attribute
	 * 
	 * @access public
	 * @param String alert_ossec_id 
	 */
	public function set_alert_ossec_id($alert_ossec_id){
		$this->alert_ossec_id = $alert_ossec_id;
	}
	
	/**
	 * Set the actual log message from the host that generated the alert (i.e. "Authentication failed")
	 * Sometimes this is a multiline entry so we use an append function when processing logs line 
	 * by line.
	 * 
	 * @access public 
	 * @param String The actual log message from the host that generated the alert (i.e. "Authentication failed")
	 */
	public function set_append_rule_log($rule_log){
		$this->rule_log .= $rule_log;
	}
	
	/**
	 * Set the host_id attribute
	 * 
	 * @access public
	 * @param Int the host_id for the ossec alert
	 */
	public function set_host_id($host_id){
		$this->host_id = intval($host_id);
	}
	
	/**
	 * Set the rule_dst_ip attribute
	 * 
	 * @access public
	 * @param String the ipv4 rule destination ip address
	 */
	public function set_rule_dst_ip($rule_dst_ip){
		if ($rule_dst_ip == filter_var($rule_dst_ip,FILTER_VALIDATE_IP)){
			$this->rule_dst_ip = $rule_dst_ip;
			$this->rule_dst_ip_numeric = ip2long($rule_dst_ip);
		}
	}
	
	/**
	 * Set the rule_dst_ip_numeric attribute
	 * 
	 * @access public
	 * @param Int the decimal representation of the rule destination ip address
	 */
	public function set_rule_dst_ip_numeric($rule_dst_ip_numeric){
		$this->rule_dst_ip_numeric = intval($rule_dst_ip_numeric);
	}
	
	/**
	 * Set the rule_id attribute
	 * 
	 * @access public 
	 * @param Int the rule id for the ossec alert
	 */
	public function set_rule_id($rule_id){
		$this->rule_id = intval($rule_id);
	}
	
	/**
	 * Set the actual log message from the host that generated the alert (i.e. "Authentication failed")
	 * 
	 * @access public 
	 * @param String The actual log message from the host that generated the alert (i.e. "Authentication failed")
	 */
	public function set_rule_log($rule_log){
		$this->rule_log = $rule_log;
	}
	
	/**
	 * Set the Src IP value for the alert
	 * 
	 * @access public
	 * @param String The ipv4 rule source ip address
	 */
	public function set_rule_src_ip($rule_src_ip){
		if ($rule_src_ip == filter_var($rule_src_ip,FILTER_VALIDATE_IP)){
			$this->rule_src_ip = $rule_src_ip;
			$this->rule_src_ip_numeric = ip2long($rule_src_ip);
		}
	}
	
	/**
	 * Set the rule_src_ip_numeric attribute
	 * 
	 * @access public
	 * @param Int the decimal representation of the rule source ip address
	 */
	public function set_rule_src_ip_numeric($rule_src_ip_numeric){
		$this->rule_src_ip_numeric = intval($rule_src_ip_numeric);
	}
	
	/**
	 * Set the rule_user attribute
	 * 
	 * @access public
	 * @param String The User from the OSSEC alert
	 */
	public function set_rule_user($rule_user){
		$this->rule_user = $rule_user;
	}
	
	/**
	 *  Set the id attribute.
	 *
	 *  @access protected
	 *  @param Int The unique ID from the data layer
	 */
	protected function set_id($id){
		$this->id = intval($id);
	}
	
}


?>