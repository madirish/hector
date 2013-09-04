<?php
/**
 * class.Collection.php
 * 
 * @package HECTOR
 * @author Justin C. Klein Keane <justin@madirish.net>
 */

/**
 * Require the database
 */
require_once('class.Db.php');
require_once('class.Log.php');

/**
 * Collection is a simple factory class
 * @package HECTOR
 * @subpackage util
 * 
 * @abstract Collection is simply a factory class.
 * This class builds a collection of objects based on strict rules and the name
 * passed in.  The name must match the object class name as well as database 
 * naming convention exactly.  For instance, to create a new collection of 
 * 'ants' objects using:
 * <pre>
 * $ant_collection = new Collection('Ant');
 * </pre>
 * The file class.Ant.php must exist in the libroot (defined in the config.ini),
 * there must be a table named 'ant' in the database with the primary key
 * ant_id.  If these conditions are met then the class will poll the database
 * with 'select ant_id from ant' and use the id's to instantiate new ant classes
 * for each id. These will be assigned to a return array that will be passed
 * back.
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
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Boolean True if the Collection was populated, false otherwise.
	 * @param String Name of the class the Constructor should marshall
	 * @param String Filter used by the subclass constructor, generally additional constraints 
	 * to the subclass SQL "where" clause.
	 * @param String Alternative subclass Collection constructor method name.
	 * @param String Optional additions to the subclass orderby clause.
	 * @param String constructor overload arguments, used when passing additional arguments 
	 * (beyond the unique id) to the subclass constructor.
	 */
	public function __construct($collection_name,$filter='',$search_func='',$orderby='', $args='') {
		$db = Db::get_instance();
		$this->log = Log::get_instance();
		$retval = FALSE;
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
			$retval = TRUE;
		}
		elseif ($this->debug) {
			$this->log->write_message('Info:  Collection array contained no values.');
		}
		return $retval;
	}
}
?>