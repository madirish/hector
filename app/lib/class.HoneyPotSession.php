<?php 
/**
 * HECTOR - class.HoneyPotSession.php
 * 
 * @author Ubani Anthony Balogun <ubani@sas.upenn.edu>
 * @package HECTOR
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
 * HoneyPots are decoy systems for gathering information 
 * about attackers
 * 
 * HoneyPotSessions collect information about attackers who successfuly
 * accessed a HoneyPot
 * 
 * @package HECTOR
 * @author Ubani Anthony Balogun <ubani@sas.upenn.edu>
 */
class HoneyPotSession extends Maleable_Object {
	
	// --- Koj_executed_command Attributes --
	
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
	 * Unique ID from the data layer
	 *
	 * @access protected
	 * @var int Unique id
	 */
	protected $id = null;
	
	/**
	 *  Executed command timestamp
	 *
	 *  @access private
	 *  @var Timestamp The timestamp of the executed command
	 */
	private $time;
	
	/**
	 * ip address of the attacker
	 *
	 * @access private
	 * @var String The dot-decimal ip address of the attacker
	 */
	private $ip;
	
	/**
	 * Decimal representation of the attacker's ip
	 * 
	 * @access private
	 * @var Int The decimal representation of the attacker's ip
	 */
	private $ip_numeric;
	
	/**
	 * The id of the honey pot accessed by the attacker
	 *
	 * @access private
	 * @var Int the id of the honey pot accessed by the attacker
	 */
	private $sensor_id;
	
	/**
	 * The Session id assigned to the connection
	 *
	 * @access private
	 * @var Int the session id assigned to the connections
	 */
	private $session_id;
	
	/**
	 * The command executed by the attacker
	 *
	 * @access private
	 * @var String the command executed by the attacker
	 */
	private $command;
	
	
	// --- OPERATIONS ---
	
	/**
	 * Construct a new blank HoneyPotSession or instansiate one from the 
	 * data layer based on ID
	 * 
	 * @access public
	 * @param Int The unique ID of the HoneyPotSession
	 * @return void
	 */
	public function __construct($id  = ''){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != ''){
			$sql = array(
				'SELECT id as honeypotsession_id, time, ip, command, ip_numeric,session_id,sensor_id
					FROM koj_executed_command k WHERE k.id=?i',
					$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])){
				$r = $result[0];
				$this->set_id($r->honeypotsession_id);
				$this->set_time($r->time);
				$this->set_ip($r->ip);
				$this->set_command($r->command);
				$this->set_ip_numeric($r->ip_numeric);
				$this->set_session_id($r->session_id);
				$this->set_sensor_id($r->sensor_id);
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
	 * Set the time attribute.
	 *
	 * @access public
	 * @param Datetime The timestamp of session
	 */
	public function set_time($datetime){
		$this->time = date("Y-m-d H:i:s", strtotime($datetime));
	}
	
	/**
	 * Get the timestamp of the login attempt
	 *
	 * @access public
	 * @return Timestamp The timestamp of the session
	 */
	public function get_time(){
		return $this->time;
	}
	
	/**
	 * Set the dot-decimal ip address of the attacker.
	 *
	 * @access public
	 * @param String The dot-decimal IP address of the attacker
	 */
	public function set_ip($ip){
		$retval = False;
		if ($ip = filter_var($ip,FILTER_VALIDATE_IP)){
			$this->ip = $ip;
			$retval = True;
		}
		return $retval;
	}
	
	/**
	 * Get the dot-decimal ip address of the attacker.
	 *
	 * @access public
	 * @return String The dot-decimal IP address of the attacker
	 */
	public function get_ip(){
		return htmlspecialchars($this->ip);
	}
	
	/**
	 * Get a link to the malicious ip database page for the ip address
	 *
	 * @access public
	 * @return String The link to the malicious ip database page for the ip address
	 */
	public function get_ip_linked(){
		$ip = $this->get_ip();
		$retval = "<a href='?action=attackerip&ip=$ip'>$ip</a>";
		return $retval;
	}
	
	/**
	 * Set the ip_numeric attribute.
	 *
	 * @access public
	 * @param Int The decimal representation of the ip address.
	 */
	public function set_ip_numeric($ip_numeric){
		$this->ip_numeric = intval($ip_numeric);
	}
	
	/**
	 * Get the decimal representation of the ip address.
	 *
	 * @access public
	 * @return Int The decimal representation of the ip address
	 */
	public function get_ip_numeric(){
		return intval($this->ip_numeric);
	}
	
	/**
	 * Set the sensor_id attribute.
	 *
	 * @access public
	 * @param Int The sensor id of the honey pot.
	 */
	public function set_sensor_id($sensor_id){
		$this->sensor_id = intval($sensor_id);
	}
	
	/**
	 * Get the sensor id of the honey pot
	 *
	 * @access public
	 * @return Int the sensor id of the honey pot.
	 */
	public function get_sensor_id(){
		return intval($this->sensor_id);
	}
	
	/**
	 * Set the session_id attribute.
	 *
	 * @access public
	 * @param Int The session id of the honey pot.
	 */
	public function set_session_id($session_id){
		$this->session_id = intval($session_id);
	}
	
	/**
	 * Get the session id of the honey pot
	 *
	 * @access public
	 * @return Int the session id of the honey pot.
	 */
	public function get_session_id(){
		return intval($this->session_id);
	}
	
	/**
	 * Set the command attribute
	 * 
	 * @access public
	 * @param String the command executed by the attacker
	 */
	public function set_command($command){
		$this->command = $command;
	}
	
	/**
	 * Get the command executed by the attacker
	 * 
	 * @access public
	 * @param String the html safe command executed by the attacker
	 */
	public function get_command(){
		return htmlspecialchars($this->command);
	}
	
	/**
	 * Return the pritable string used for the object in interfaces
	 *
	 * @access public
	 * @return String The printable string of the object name
	 */
	public function get_label(){
		return 'Kojoney Executed Command';
	}
	
	/**
	 *  This function directly supports the Collection class.
	 *
	 *  @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = ''){
		$sql = 'SELECT k.id as honeypotsession_id FROM koj_executed_command k WHERE k.id > 0';
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
			$sql .= ' ORDER BY k.time desc';
		}
		return $sql;
	}
	
	/**
	 * This function returns the attributes of the object in an associative array
	 * 
	 * @return Array an associative array of the object's attributes
	 */
	public function get_object_as_array(){
		return array(
				'id' => $this->get_id(),
				'time' => $this->get_time(),
				'ip' => $this->get_ip(),
				'ip_numeric' => $this->get_ip_numeric(),
				'sensor_id' => $this->get_sensor_id(),
				'session_id' => $this->get_session_id(),
				'command' => $this->get_command(),
				'ip_linked' => $this->get_ip_linked(),
				
		);
	}
	
	
}

?>