<?php 
/**
 * HECTOR - class.HoneyPotConnect.php
 * 
 * @author Ubani Anthony Balogun <ubani@sas.upenn.edu>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package Hector
 *  
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
 * HoneyPots are decoy systems for gathering information 
 * about attackers
 * 
 * HoneyPotConnect represents an attempt to connect to the Honey Pot (Kojoney)
 *
 * @package HECTOR
 * @author Ubani Anthony Balogun <ubani@sas.upenn.edu>
 */
class HoneyPotConnect extends Maleable_Object {
	
	// --- Koj_login_attempt Attributes ---
	
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
	 *  login attempt timestamp
	 *
	 *  @access private
	 *  @var Timestamp The timestamp of the login attempt
	 */
	private $time;
	
	/**
	 * ip address attempting to login
	 * 
	 * @access private 
	 * @var String The dot-decimal ip address attempting to connect
	 */
	private $ip;
	
	/**
	 *  The username used in the login attempt
	 *  @access private
	 *  @var String The username used in the login attempt
	 */
	private $username;
	
	/**
	 * The password used in the login attempt;
	 * 
	 * @access private
	 * @var String The password provided in the login attempt
	 */
	private $password;
	
	/**
	 * Decimal representation of the ip used in the login attempt
	 * 
	 * @access private
	 * @var Int The decimal representation of the ip address used in the login attempt
	 */
	private $ip_numeric;
	
	/**
	 * The id of the honey pot targeted by the login attempt
	 * 
	 * @access private
	 * @var Int the id of the honey pot 
	 */
	private $sensor_id;
	
	/**
	 * The two letter country code of the ip address used in the login attempt
	 * 
	 * @access private
	 * @var String The two letter country code of the ip address used in the login attempt 
	 */
	private $country_code;
	
	// --- OPERATIONS ---
	
	/**
	 * Construct a new blank HoneyPotConnect or instantiate one
	 * from the data layer based on ID
	 * 
	 * @access public
	 * @author Ubani Anthony Balogun <ubani@sas.upenn.edu>
	 * @param Int The unique ID of the HoneyPotConnect
	 * @return void
	 */
	public function __construct($id  = ''){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != ''){
			$sql = array(
				'SELECT id as honeypotconnect_id, time, ip, username, password, ip_numeric, sensor_id, country_code 
					FROM koj_login_attempt k WHERE k.id = ?i',
					$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])){
				$this->set_id($result[0]->honeypotconnect_id);
				$this->set_time($result[0]->time);
				$this->set_ip($result[0]->ip);
				$this->set_username($result[0]->username);
				$this->set_password($result[0]->password);
				$this->set_ip_numeric($result[0]->ip_numeric);
				$this->set_sensor_id($result[0]->sensor_id);
				$this->set_country_code($result[0]->country_code);
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
	 * @param Datetime The timestamp of the login attempt
	 */
	public function set_time($datetime){
		$this->time = date("Y-m-d H:i:s", strtotime($datetime));
	}
	
	/**
	 * Get the timestamp of the login attempt
	 * 
	 * @access public
	 * @return Timestamp The timestamp of the login attempt
	 */
	public function get_time(){
		return $this->time;
	}
	
	/**
	 * Set the dot-decimal ip address of the login attempt.
	 * 
	 * @access public
	 * @param String The dot-decimal IP address of the login attempt
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
	 * Get the dot-decimal ip address of the login attempt.
	 * 
	 * @access public
	 * @return String The dot-decimal IP address of the login attempt
	 */
	public function get_ip(){
		return htmlspecialchars($this->ip);
	}
	
	/**
	 * Get the hostname of the ip address used in the login attempt
	 * 
	 * @access public
	 * @return String the hostname of the ip address
	 */
	public function get_hostname(){
		return gethostbyaddr($this->get_ip());
	}
	
	
	/**
	 * Set the username attribute.
	 * 
	 * @access public
	 * @param String The username used in the login attempt
	 */
	public function set_username($username){
		$this->username = $username;
	}
	
	/**
	 * Get the html safe username used in login attempt
	 * 
	 * @access public
	 * @return String The username used in the login attempt
	 */
	public function get_username(){
		return htmlspecialchars($this->username);
	}
	
	/**
	 * Set the password attribute.
	 * 
	 * @access public
	 * @param String The password used in the login attempt
	 */
	public function set_password($password){
		$this->password = utf8_encode($password);
	}
	
	/**
	 * Get the HTML safe password used in the login attempt
	 * 
	 * @access public
	 * @return String The password used in the login attempt
	 */
	public function get_password(){
		return htmlspecialchars($this->password);
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
	 * Set the country_code attribute.
	 * 
	 * @access public
	 * @param String The two letter country code associated with the login attempt.
	 */
	public function set_country_code($code){
		$code = substr(strtoupper($code),0,2);
		$code = preg_replace('/[^A-Z]/', '', $code);
		$this->country_code = $code;
	}
	
	/**
	 * Get the country code associated with the login attempt
	 * 
	 * @access public
	 * @return String The HTML safe country code associated with the login attempt.
	 */
	public function get_country_code(){
		return htmlspecialchars($this->country_code);
	}
	
	
	/**
	 * Return the pritable string used for the object in interfaces
	 * 
	 * @access public
	 * @return String The printable string of the object name
	 */
	public function get_label(){
		return 'Kojoney Login Attempt';
	}
	
	/**
	 *  This function directly supports the Collection class.
	 *  
	 *  @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = ''){
		$sql = 'SELECT k.id as honeypotconnect_id FROM koj_login_attempt k WHERE k.id > 0';
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
	 *  This function returns the attributes of the object in an associative array
	 *  
	 *  @return Array An associative array of the object's attributes
	 */
	public function get_object_as_array(){
		return array(
				'id' => $this->get_id(),
				'time' => $this->get_time(),
				'ip' => $this->get_ip(),
				'username' => $this->get_username(),
				'password' => $this->get_password(),
				'ip_numeric' => $this->get_ip_numeric(),
				'sensor_id' => $this->get_sensor_id(),
				'country_code' => $this->get_country_code(),
		);
	}
	
}

?>