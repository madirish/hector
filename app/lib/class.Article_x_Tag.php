<?php 
/**
 * HECTOR - class.Article_X_Tag.php
 * 
 * @author Ubani A Balogun
 * @package HECTOR
 * 
 * 
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

class Article_x_Tag extends Maleable_Object {
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
	
	public function __construct($id  = ''){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
	}
	
	/**
	 * Returns the frequencies of entires for a field in the data layer
	 *
	 * @param String $field The field from the data layer to count
	 * @param string $bound The bound for the data
	 * @return Array The frequenies of entries for the field
	 */
	public function get_field_frequencies($field){
		$retval = array();
		$sql = 'SELECT ?s, count(?s) as frequency FROM article_x_tag WHERE article_id > 0 AND tag_id > 0 ';
		$sql .= ' GROUP BY ?s order by frequency desc';
		$result = $this->db->fetch_object_array(array($sql,$field,$field,$field));
		if (isset($result[0])){
			foreach ($result as $row){
				$retval[$row->$field] = $row->frequency;
			}
		}
		return $retval;
	}
}

?>