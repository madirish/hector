<?php
/**
 * class.Dblog.php
 *
 * @package HECTOR
 * @abstract This class provides logging to the database.
 * @author Justin C. Klein Keane <justin@madirish.net>
 */

/**
 * Set up debugging
 */
define("DEBUG", 0);

/**
 * Required includes
 */
require_once("class.Db.php");

/**
 * This class provides logging to the database.
 * 
 * @package HECTOR
 * @subpackage util
 *
 */
Class Dblog {

    // --- ATTRIBUTES ---
	
	/**
	 * Singleton implementation for Db object
	 *
	 * @var object
	 */
	static private $db = NULL;

	/**
	 * Singleton implementation
	 *
	 * @var object
	 */
	static private $instance = NULL;
	
	
	// --- OPERATIONS ---

    /**
     * Set up the connection and make sure the database
     * is available
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return void
     */
    public function __construct() {
        $this->db = Db::get_instance();
    }
    
    

	/**
	 * This is the Singleton interface
	 *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return Dblog An instance of the Dblog object.
	 */
	public function get_instance() {
		if (self::$instance == NULL) self::$instance = new Dblog();
		return self::$instance;
	}
	
	/**
	 * Write a log message
	 * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @param String Message to log
	 * @return void
	 */
	public function log($type, $message) {
		$sql = array(
	    		'INSERT INTO log SET log_timestamp = now(), ' .
	    		'log_type = \'?s\', log_message = \'?s\'',
	    		$type, 
	    		$message
	    	);
	    if ($message != '' && $type != '') $this->db->iud_sql($sql);
	}
	
	
}
?>