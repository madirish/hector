<?php 
/**
 * HECTOR - class.Ossec_Alert.php
 * 
 * @author Ubani A Balogun <ubani@sas.upenn.edu>
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
 */

class Ossec_Alert extends Maleable_Object {
	// --- Attributes ---
	
	/**
	 *  Instance of the Db
	 *
	 *  @access private
	 *  @var Db An instance of the Db
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
	 * Unique id from the data layer
	 * 
	 * @access protected
	 * @var int Unique id
	 */
	protected $id = null;
	
	/**
	 * Timestamp of the Alert
	 * 
	 * @access private
	 * @var timestamp of the ossec alert
	 */
	private $alert_date;
	
	/**
	 * The host id for the ossec_alert
	 * 
	 * @access private
	 * @var Int the host id related to the ossec alert
	 */
	private $host_id;
	
	/**
	 * The alert log
	 * 
	 * @access private
	 * @var String the ossec alert log
	 */
	private $alert_log;
	
	/**
	 * The rule id of the ossec rule
	 * 
	 * @access private 
	 * @var Int the rule id of the ossec rule
	 */
	private $rule_id;
	
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
	 * The rule log of the ossec alert
	 * 
	 * @access private
	 * @var String the rule log of the ossec alert
	 */
	private $rule_log;
	
	/**
	 * The alert_ossec_id value from the data layer
	 * 
	 * @access private
	 * @var String the alert_ossec_id value from the data layer
	 */
	private $alert_ossec_id;
	
	
	// -- Operations --
	
	/**
	 * Contruct a new Ossec Alert or instanstiate one from the data layer by ID
	 * 
	 * @access public 
	 * @param Int the unique ID of the OSSEC ALERT
	 * @return void
	 */
	public function __construct($id=''){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id !=''){
			$sql = array(
				'SELECT alert_id as ossec_alert_id, alert_date, host_id, 
					alert_log, rule_id, rule_src_ip, rule_src_ip_numeric, 
					rule_user, rule_log, alert_ossec_id
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
				$this->set_rule_src_ip($r->rule_src_ip);
				$this->set_rule_src_ip_numeric($r->rule_src_ip_numeric);
				$this->set_rule_user($r->rule_user);
				$this->set_rule_log($r->rule_log);
				$this->set_alert_ossec_id($r->alert_ossec_id);
				
			}
		}
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
	 * Set the alert_date attribute.
	 *
	 * @access public
	 * @param Datetime The timestamp of the login attempt
	 */
	public function set_alert_date($datetime){
		$this->alert_date = date("Y-m-d H:i:s", strtotime($datetime));
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
	 * Set the host_id attribute
	 * 
	 * @access public
	 * @param Int the host_id for the ossec alert
	 */
	public function set_host_id($host_id){
		$this->host_id = intval($host_id);
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
	 * Set the alert_log attribute
	 * 
	 * @access public
	 * @param String the alert log for the ossec alert
	 */
	public function set_alert_log($alert_log){
		$this->alert_log = $alert_log;
	}
	
	/**
	 * Get the alert log for the ossec alert
	 * 
	 * @access public
	 * @return String the html safe alert log for the ossec alert
	 */
	public function get_alert_log(){
		return htmlspecialchars($this->alert_log);
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
	 * Get the rule id for the ossec alert
	 * 
	 * @access public
	 * @return Int the rule id for the ossec alert
	 */
	public function get_rule_id(){
		return $this->rule_id;
	}
	
	/**
	 * Set the rule_src_ip attribute
	 * 
	 * @access public
	 * @param String the ipv4 rule source ip address
	 */
	public function set_rule_src_ip($rule_src_ip){
		if ($rule_src_ip == filter_var($rule_src_ip,FILTER_VALIDATE_IP)){
			$this->rule_src_ip = $rule_src_ip;
		}
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
	 * Set the rule_src_ip_numeric attribute
	 * 
	 * @access public
	 * @param Int the decimal representation of the rule source ip address
	 */
	public function set_rule_src_ip_numeric($rule_src_ip_numeric){
		$this->rule_src_ip_numeric = intval($rule_src_ip_numeric);
	}
	
	/**
	 * Get the decimal representation of the rule source ip
	 * 
	 * @access public
	 * @return Int the decimal representation of the rule source ip
	 */
	public function get_rule_src_ip_numeric(){
		return $this->rule_src_ip_numeric;
	}
	
	/**
	 * Set the rule_user attribute
	 * 
	 * @access public
	 * @param String the rule user 
	 */
	public function set_rule_user($rule_user){
		$this->rule_user = $rule_user;
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
	 * Set the rule log attribute
	 * 
	 * @access public 
	 * @param String the rule log
	 */
	public function set_rule_log($rule_log){
		$this->rule_log = $rule_log;
	}
	
	/**
	 * Get the rule log
	 * 
	 * @access public
	 * @return The html safe rule log
	 */
	public function get_rule_log(){
		return htmlspecialchars($this->rule_log);
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
	 * Returns the frequencies of entires for a field in the data layer
	 *
	 * @param String $field The field from the data layer to count
	 * @param string $bound The bound for the data
	 * @return Array The frequenies of entries for the field
	 */
	public function get_field_frequencies($field,$bound=''){
		$retval = array();
		$sql = array('SELECT ?s , count(?s) as frequency FROM ossec_alert'
				. ' WHERE id > 0 ' . $bound . ' GROUP BY ?s order by frequency desc', $field, $field, $field);
		$result = $this->db->fetch_object_array($sql);
		if (isset($result[0])){
			foreach ($result as $row){
				$retval[$row->$field] = $row->frequency;
			}
		}
		return $retval;
	}
	
	
}


?>