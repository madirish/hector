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
     * Short description of attribute id
     *
     * @access public
     * @var int
     */
    private $id = 0;

    /**
     * Short description of attribute token
     *
     * @access public
     * @var int
     */
    private $token = 0;

    /**
     * Short description of attribute name
     *
     * @access public
     * @var String
     */
    private $name = null;

    // --- OPERATIONS ---
    
    public function __construct() {
    	$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		
		// do a little housekeeping
		$this->expunge_forms();
    }

    /**
     * Short description of method expunge_forms
     *
     * @access private
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @return void
     */
    private function expunge_forms()
    {
		$sql = 'DELETE FROM form WHERE form_datetime < DATE_SUB(NOW(), INTERVAL 20 MINUTE)';
		$this->db->iud_sql($sql);
    }

    /**
     * Short description of method get_token
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @return String
     */
    public function get_token()
    {
    	if ($this->token == 0) {
    		$this->token = md5(time());
    	}
        return $this->token;
    }

    /**
     * Short description of method save
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @return void
     */
    public function save()
    {
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
        		$this->token,
        		$_SERVER['REMOTE_ADDR'], 
        		date( 'Y-m-d H:i:s', time())
        		);
        $this->db->iud_sql($sql);
    }

    /**
     * Short description of method set_name
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @param  String name
     * @return void
     */
    public function set_name($name)
    {
        $this->name = htmlspecialchars($name);
    }

    /**
     * Short description of method validate
     *
     * @access public
     * @author Justin Klein Keane, <jukeane@sas.upenn.edu>
     * @param  form_name
     * @param  token
     * @param  ip
     * @return boolean
     */
    public function validate($form_name,$token,$ip)
    {
    	$retval = false;
        $sql = array('SELECT form_id FROM form WHERE form_name=\'?s\' AND form_token=\'?s\' AND form_ip =\'?s\'',
        			$form_name,
        			$token,
        			$ip);
        $result = $this->db->fetch_object_array($sql);
		if (isset($result[0]) && $result[0]->form_id > 0) {
			$retval = true;
			if ($_SESSION['debug']) $this->log->write_message('Form ' . htmlspecialchars($form_name) . ' validated.');
		}
			
        return $retval;
    }

} /* end of class Form */

?>