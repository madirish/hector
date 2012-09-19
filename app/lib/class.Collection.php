<?php
/**
 * @package hector
 * @author Justin C. Klein Keane <justin@madirish.net>
 *
 * @abstract <p>Collection is simply a factory class.</p>
 * <p>This class builds a collection of objects based on strict rules and the name passed in.  The name must match the object class name as well as database naming convention exactly.  For instance, to create a new collection of 'ants' objects using:</p><p><pre>
 * $ant_collection = new Collection('Ant');
 * </pre></p><p>
 * The file class.Ant.php must exist in the libroot (defined in the config.ini),
 * there must be a table named 'ant' in the database with the primary key
 * ant_id.  If these conditions are met then the class will poll the database
 * with 'select ant_id from ant' and use the id's to instantiate new ant classes
 * for each id. These will be assigned to a return array that will be passed
 * back.
 *
 */

// required libraries
require_once('class.Db.php');
require_once('class.Log.php');

/**
 * Collection is a simple factory class
 * @package intranet
 * @subpackage util
 */
Class Collection {

	/**
	 * $members is an array of objects collected by the __construct().
	 *
	 * @var array
	 */
	var $members;
	
	/**
	 * Turn on debugging output - be careful this is extremely verbose!
	 */
	private $debug = false;

	/**
	 * The construct accepts a string that is used to determine the primary key, table name, 
	 * PHP script name, and object name that will be used to create objects (which will then 
	 * be added to the $members variable.  The optional $filter string is used to limit the 
	 * basic select functionality.  The optional $search_func parameter can be used to specify 
	 * an alternate function other than the default get_collection_definition() in special 
	 * cases of complex collection queries (such as queries that touch multiple tables or 
	 * implement specific joins).
	 * The $args parameter is an optional parameter used to call overloaded constructors.  This
	 * is designed to address situations where full object instantiation isn't necessary due to
	 * the incurred overhead (say if you just want a few properties of the object).
	 *
	 * @param string $collection_name
	 * @param string optional_filter
	 * @param string optional_alternate_definition_function_name
	 * @param string constructor overload arguments
	 */
	public function __construct($collection_name,$filter='',$search_func='',$orderby='', $args='') {
		$db = Db::get_instance();
		$this->log = Log::get_instance();
		
		$this->debug = $_SESSION['debug'];
		
		if ($this->debug) $this->log->write_message('Creating collection '.$collection_name.' with filter '.$filter);

		require_once ($_SESSION['libroot'] . 'class.' . $collection_name . '.php');

		$definition = new $collection_name;

		//look up the collection in data definitions
		if ($search_func == '') 
			$collection_sql = $definition->get_collection_definition($filter, $orderby);
		else
			$collection_sql = $definition->$search_func($filter, $orderby);
		
		if ($this->debug) $this->log->write_message('Info:  Collection returned definition: ' . preg_replace("/(\t|\n|\r)/"," ",$collection_sql));
	
		$collection = $db->fetch_object_array($collection_sql);

		if	(is_array($collection)) {
			foreach ($collection as $item) {
				$id = strtolower($collection_name . '_id');
				if ($args == '') $this->members[] = new $collection_name($item->$id);
				else {
					$this->members[] = new $collection_name($item->$id, $args);
					if ($this->debug) $this->log->write_message('Returning collection with args: ' . $args . ' and id ' . $item->id);
				}
			}
		}
		elseif ($this->debug) $this->log->write_message('Info:  Collection array contained no values.');

	}
}
?>