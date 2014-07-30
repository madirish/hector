<?php 
/**
 * Hector - class.Url.php
 * 
 * @author Ubani Anthony Balogun <uban@sas.upenn.edu>
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
 * The Url class handles interactions with the Hector url table.
 * The Hector url table stores the screenshots taken from Hector hosts
 * 
 * @package HECTOR
 * @author Ubani Anthony Balogun <ubani@sas.upenn.edu>
 */
class Url extends Maleable_Object {
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
	 * Url related to a host
	 * 
	 * @access private
	 * @var String the url of the host
	 */
	private $url;
	
	/**
	 * Name of the screenshot image
	 * 
	 * @access private
	 * @var String the name of the screenshot image file
	 */
	private $screenshot;
	
	/**
	 * Host Id
	 * 
	 * @access private
	 * @var Int the Host ID of the screenshotted host
	 */
	private $host_id;
	
	// -- Operations ---
	
	/**
	 * Construct a new Url or instantiate one from the data layer by ID
	 * 
	 * @access public
	 * @param Int The unique ID of the Url
	 * @return void
	 */
	public function __construct($param ='',$type = 'id'){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		switch($type){
			case "screenshot":
				$sql = array(
					"SELECT * from url u where u.url_screenshot = '?s'", $param
				);
				break;
			case "id":
				$sql = array(
					'SELECT * from url u where u.url_id = ?i', $param
				);
				break;
			case "url":
				$sql = array(
					"SELECT * from url u where u.url_url = '?s'", $param
				);
				break;
			default:
				$sql = array(
					'SELECT * from url u where u.url_id = ?i', $param
				);
				break;
		}
		if ($param != ''){
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])){
				$r = $result[0];
				$this->set_id($r->url_id);
				$this->set_url($r->url_url);
				$this->set_screenshot($r->url_screenshot);
				$this->set_host_id($r->host_id);
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
	 *  Set the host_id attribute.
	 *
	 *  @access protected
	 *  @param Int The host_id from the data layer
	 */
	protected function set_host_id($id){
		$this->host_id = intval($id);
	}
	
	/**
	 *  Get the host_id attribute
	 *
	 *  @access public
	 *  @return Int The host_id attribute of the object
	 */
	public function get_host_id(){
		return intval($this->host_id);
	}
	
	/**
	 * Set the url attribute
	 * 
	 * @access public
	 * @param String The url associated with the screenshot
	 */
	public function set_url($url){
		$retval = False;
		if ($url = filter_var($url,FILTER_VALIDATE_URL)){
			$this->url = $url;
			$retval = True;
		}
		return $retval;
	}
	
	/**
	 * Get the url attributed
	 * 
	 * @access public
	 * @param String the html safe url associated with the screenshot
	 */
	public function get_url(){
		return htmlspecialchars($this->url);
	}
	
	/**
	 * Set the screenshot variable
	 * 
	 * @access public
	 * @param String the File name of the screenshot
	 */
	public function set_screenshot($screenshot){
		$this->screenshot = $screenshot;
	}
	
	/**
	 * Get the screenshot attribute
	 * 
	 * @access public
	 * @return String The html safe filename of the screenshot
	 */
	public function get_screenshot(){
		return htmlspecialchars($this->screenshot);
	}
	
	/**
	 * Get the screenshot link
	 * 
	 * @access public
	 * @return The html for the screenshot image
	 */
	public function get_screenshot_link(){
		$host_id = $this->get_host_id();
		$url = $this->get_url();
		$retval = "";
		$retval .= "<a href='?action=host_details&id=$host_id'>";
		$retval .= "<img alt='Screenshot' src='?action=display_screenshot&ajax&url=$url'>";
		$retval .= "</a>";
		return $retval;
	}
	
	
	/**
	 * Get the object as an array
	 * 
	 * @access public
	 * @param Array an associative array of the object attributes
	 */
	public function get_object_as_array(){
		return array(
				'id' => $this->get_id(),
				'url' => $this->get_url(),
				'screenshot' => $this->get_screenshot(),
				'host_id' => $this->get_host_id(),
				'screenshot_link' => $this->get_screenshot_link(),
				'host_name' => $this->get_host_name(),
		);
	}
	
	/**
	 * This function directly supports the Collection class
	 * 
	 * @access public
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = ''){
		$sql = 'SELECT u.url_id from url u WHERE u.url_id > 0';
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
			$sql .= ' ORDER BY u.host_id asc';
		}
		return $sql;
	}
	
	/**
	 * Returns the Hostname related to the url
	 * 
	 * @access public
	 * @return String The Hostname related to the url
	 */
	public function get_host_name(){
		require_once('class.Host.php');
		$host = new Host($this->get_host_id());
		$name = $host->get_name();
		return $name;
	}
	
}

?>