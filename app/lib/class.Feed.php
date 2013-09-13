<?php
/**
 * HECTOR - class.Feed.php
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

/**
 * Feed is just an object for tracking RSS feed urls
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Implement at the data layer and create a method to fetch, parse, and import feeds
 */
class Feed extends Maleable_Object implements Maleable_Object_Interface {
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
     * Unique id
     *
     * @access protected
     * @var int
     */
    protected $id = null;

	/**
	 * Friendly name
	 * 
	 * @access private
	 * @var String The name of the feed
	 */
    private $name;

	/**
	 * URL to the feed
	 * 
	 * @access private
	 * @var String The URL of the feed.
	 */
    private $url;

    // --- OPERATIONS ---

    /**
     * Set up a new instance of this object.
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID
     * @return void
     */
	public function __construct($id = '') {
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '' && $id > 0) {
			$sql = array(
				'SELECT * FROM rss WHERE rss_id = ?i',
							$id
						);
			$result = $this->db->fetch_object_array($sql);
			$this->set_id($result[0]->rss_id);
			$this->set_name($result[0]->rss_name);
			$this->set_url($result[0]->rss_url);
		}
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
	    		'DELETE FROM rss WHERE rss_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a feed.
	 * 
	 * @access public
	 * @return Array An array for the CRUD template.
	 */
	public function get_add_alter_form() {

		return array (
			array('label'=>'RSS feed name',
					'type'=>'text',
					'name'=>'rssname',
					'value_function'=>'get_name',
					'process_callback'=>'set_name'),
			array('label'=>'RSS feed url',
					'type'=>'text',
					'name'=>'rssurl',
					'value_function'=>'get_url',
					'process_callback'=>'set_url')
		);
	}

	/**
	 *  This function directly supports the Collection class.
	 *
	 * @access public
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT rss_id as feed_id FROM rss WHERE rss_id > 0';
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
			$sql .= ' ORDER BY rss_name';
		}
		return $sql;
	}
	
	/**
	 * The method to return the HTML for the details on this specific host
	 * 
	 * @access public
	 * @return String HTML string for display in the details template.
	 */
	public function get_details() {
		$retval = '<table id="feed_details">' . "\n";
		$retval .= '<tr id="name"><td style="font-weight:bold;">Feed name:</td><td>' . $this->get_name() . '</td></tr>' . "\n";
		$retval .= '<tr id="url"><td style="font-weight:bold;">Feed url:</td><td>' . $this->get_url() . '</td></tr>' . "\n";
		$retval .= '</table>';
		return $retval;
	}

	/**
	 * Array for the displays template
	 * 
	 * @access public
	 * @return Array Display array for template.
	 */
	public function get_displays() {
		return array('Name'=>'get_name', 'URL'=>'get_url');
	}
	
	/**
	 * Return the name string
	 * 
	 * @access public
	 * @return String  The name of the feed (for admin display)
	 */
	public function get_name() {
		return htmlspecialchars($this->name);
	}
	
	/**
	 * Return the url string
	 * 
	 * @access public
	 * @return String The URL to the feed
	 */
	public function get_url() {
		return htmlspecialchars($this->url);
	}

	/**
	 * Save the object for persistence
	 * 
	 * @access public
	 * @return Boolen TRUE if everything worked, FALSE on error
	 */
	public function save() {
		$retval = FALSE;
		if ($this->id > 0 ) {
			// Update an existing rss feed
	    	$sql = array(
	    		'UPDATE rss SET rss_name = \'?s\', rss_url = \'?s\' WHERE rss_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_url(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
		}
		else {
			$sql = array(
				'INSERT INTO rss SET rss_name = \'?s\', rss_url = \'?s\'',
				$this->get_name(),
				$this->get_url(),
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
	 * Set the name of the feed
	 * 
	 * @access public
	 * @param String The feed name (for admin display)
	 */
	public function set_name($name) {
		$this->name = $name;
	}

	/**
	 * Set the feed URL
	 * 
	 * @access public
	 * @param String The URL for the RSS feed.
	 */
	public function set_url($url) {
		$this->url = $url;
	}

} /* end of class Feed */

?>