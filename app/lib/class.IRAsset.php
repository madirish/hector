<?php
/**
 * HECTOR - class.IRAsset.php
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Error reporting
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
 * IRAssets are free taxonomies used to group hosts.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class IRAsset extends Maleable_Object implements Maleable_Object_Interface {


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
     * @access protected
     * @var int Unique id
     */
    protected $id = null;

    /**
     * asset name
     * 
     * @access private
     * @var String The name of the asset
     */
    private $name;

    // --- OPERATIONS ---

    /**
     * Construct a new blank IRAsset or instantiate one
     * from the data layer based on ID
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID of the IRAsset
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
        $this->log = Log::get_instance();
        if ($id != '') {
            $sql = array(
                'SELECT * FROM incident_asset WHERE asset_id = ?i',
                $id
            );
            $result = $this->db->fetch_object_array($sql);
            /**
             * There may not be a result, creating a new object
             * without a valid ID can be used to verify ID values
             */
            if (count($result) == 1 && isset($result[0]->asset_id)) {
                $this->set_id($result[0]->asset_id);
                $this->set_name($result[0]->asset_asset);
            }
        }
    }


    /**
     * Delete the record from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
        $retval = FALSE;
        if ($this->id > 0 ) {
            // Delete an existing record
            $sql = array(
                'DELETE FROM incident_asset WHERE asset_id = \'?i\'',
                $this->get_id()
            );
            $retval = $this->db->iud_sql($sql);
            // Delete incidents with this asset
            $sql = array(
                'DELETE FROM incident WHERE asset_id = \'?i\'',
                $this->get_id()
            );
            $this->db->iud_sql($sql);
        }
        return $retval;
    }

    /**
     * This is a functional method designed to return
     * the form associated with altering a tag.
     * 
     * @access public
     * @return Array The array for the default CRUD template.
     */
    public function get_add_alter_form() {
        return array (
            array('label'=>'Asset',
                    'type'=>'text',
                    'name'=>'asset',
                    'value_function'=>'get_name',
                    'process_callback'=>'set_name')
        );
    }

    /**
     *  This function directly supports the Collection class.
     *
     * @return String SQL select string
     */
    public function get_collection_definition($filter = '', $orderby = '') {
        $query_args = array();
        $sql = 'SELECT a.asset_id AS irasset_id FROM incident_asset a WHERE a.asset_id > 0';
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
            $sql .= ' ORDER BY a.asset_asset';
        }
        return $sql;
    }

    /**
     * Get the displays for the default details template
     * 
     * @return Array Dispalays for default template
     */
    public function get_displays() {
        return array('Asset'=>'get_name');
    }
    
    /**
     * Get text for the details screen explaining what this is
     * 
     * @return String A description of this object's purpose
     */
    public function get_explaination() {
        return "Assets are the things that are affected by the incident, from laptops to tablets to login credentials.";
    }

    /**
     * Get the unique ID for the object
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The unique ID of the object
     */
    public function get_id() {
       return intval($this->id);
    }

    /**
     * The HTML safe name of the IRAsset
     * 
     * @access public
     * @return String The HTML display safe name of the IRAsset.
     */
    public function get_name() {
        return htmlspecialchars($this->name);
    }
    
    /**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Incident Report Asset';
    } 

    /**
     * Persist the IRAsset to the data layer
     * 
     * @access public
     * @return Boolean True if everything worked, FALSE on error.
     */
    public function save() {
        $retval = FALSE;
        if ($this->id > 0 ) {
            // Update an existing user
            $sql = array(
                'UPDATE incident_asset SET asset_asset = \'?s\' WHERE asset_id = \'?i\'',
                $this->get_name(),
                $this->get_id()
            );
            $retval = $this->db->iud_sql($sql);
        }
        else {
            $sql = array(
                'INSERT INTO incident_asset SET asset_asset = \'?s\'',
                $this->get_name()
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
     * Set the id attribute.
     * 
     * @access protected
     * @param Int The unique ID from the data layer
     */
    protected function set_id($id) {
        $this->id = intval($id);
    }

    /**
     * Set the name of the asset
     * 
     * @access public
     * @param String The name of the asset
     */
    public function set_name($asset) {
        $this->name = $asset;
    }

} /* end of class IRAsset */

?>