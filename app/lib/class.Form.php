<?php
/**
 * HECTOR - class.Form.php
 *
 *
 * This file is part of HECTOR.
 *
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Set up error reporting
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Db.php');

/**
 * Form class is used to create and track anti-XSRF tokens
 * in forms for the application
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
class Form {
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
     * Unique ID from the data layer
     *
     * @access private
     * @var Int The unique ID from the data layer
     */
    private $id = null;

    /**
     * Anti XSRF token
     *
     * @access private
     * @var String MD5sum based anti XSRF token
     */
    private $token = 0;

    /**
     * The form name
     *
     * @access private
     * @var String The Form name
     */
    private $name = null;

    // --- OPERATIONS ---
    
    /**
     * Create a new instance of the Form object
     * 
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return void
     */
    public function __construct() {
    	$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		
		// do a little housekeeping
		$this->expunge_forms();
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
	    		'DELETE FROM form WHERE form_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    }

    /**
     * Get rid of older forms (20 min expiry)
     *
     * @access private
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if there were any problems.
     */
    private function expunge_forms() {
		$sql = 'DELETE FROM form WHERE form_datetime < DATE_SUB(NOW(), INTERVAL 20 MINUTE)';
		return $this->db->iud_sql($sql);
    }
	
	/**
	 * Return the form name
	 *
	 * @access public
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @return String The name of the form
	 */
	public function get_name() {
		return htmlspecialchars($this->name);
	}

    /**
     * Return the XSRF token
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @return String MD5sum based anti XSRF token
     */
    public function get_token() {
    	if ($this->token == 0) {
    		$this->token = md5(time());
    	}
        return $this->token;
    }

    /**
     * Persist the form to the data layer
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something went wrong
     */
    public function save() {
    	$retval = FALSE;
        if (! $this->name) {
        	$this->log->write_error('unspecified form_name in save(), class.Form.php');
        	$this->set_name('Unknonwn form');
        }
        if ($this->token == 0) $this->get_token();
        $sql = array('INSERT INTO form ' .
        		'set form_name = \'?s\', ' .
        			'form_token = \'?s\', ' .
        			'form_ip = \'?s\', ' .
        			'form_datetime = \'?d\'',
        		$this->name,
        		$this->get_token(),
        		$_SERVER['REMOTE_ADDR'], 
        		date( 'Y-m-d H:i:s', time())
        		);
        $retval = $this->db->iud_sql($sql);
        // Now set the id
    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0]) && $result[0]->last_id > 0) {
    		$this->set_id($result[0]->last_id);
    	}
    	return $retval;
    }

    /**
     * Set the form name
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @param  String Form name (used for lookups)
     * @return void
     */
    public function set_name($name) {
        $this->name = $name;
    }

    /**
     * Validate the form
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @param  String The form name
     * @param  String The anti-XSRF token
     * @param  String The IP address of the form submission
     * @return Boolean True if the form validates, False otherwise
     */
    public function validate($form_name,$token,$ip) {
    	$retval = FALSE;
        $sql = array('SELECT form_id FROM form WHERE form_name=\'?s\' AND form_token=\'?s\' AND form_ip =\'?s\'',
        			$form_name,
        			$token,
        			$ip);
        $result = $this->db->fetch_object_array($sql);
		if (isset($result[0]) && $result[0]->form_id > 0) {
			$retval = TRUE;
			if ($_SESSION['debug']) $this->log->write_message('Form ' . htmlspecialchars($form_name) . ' validated.');
		}
			
        return $retval;
    }

} /* end of class Form */

?>