<?php
/**
 * HECTOR - class.Incident.php
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
 * Incidents are reports of security related events that are
 * anonymized for anyalysis and sharing.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Incident extends Maleable_Object implements Maleable_Object_Interface {
  
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
     * Incident name
     * 
     * @access private
     * @var String The name of the tag
     */
     private $title = null;
    
     private $month = null;
     
     private $year = null;
     
     private $agent = null;
     private $agent_id = null;
     
     private $action = null;
     private $action_id = null;
     
     private $asset = null;
     private $asset_id = null;
     
     private $confidential_data = null;
     
     private $integrity_loss = null; // TEXT,
     private $authenitcity_loss = null; // TEXT,
     private $availability_loss_timeframe_id = null; // INT NOT NULL,
     private $utility_loss = null; // TEXT,
     private $action_to_discovery_timeframe_id = null; // INT NOT NULL,
     private $discovery_to_containment_timeframe_id = null; // INT NOT NULL,
     private $discovery_id = null; // INT NOT NULL,
     private $discovery_evidence_sources = null; // TEXT,
     private $discovery_metrics = null; // TEXT,
     private $hindsight = null; // TEXT,
     private $correction_recommended = null; // TEXT,
     private $asset_loss_magnitude_id = null; // INT NOT NULL,
     private $disruption_magnitude_id = null; // INT NOT NULL,
     private $response_cost_magnitude_id = null; // INT NOT NULL,
     private $impact_magnitude_id = null; // INT NOT NULL,

    // --- OPERATIONS ---

    /**
     * Construct a new blank Incident or instantiate one
     * from the data layer based on ID
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID of the Incident
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
        $this->log = Log::get_instance();
        if ($id != '') {
          $sql = array(
            'SELECT * FROM incident WHERE incident_id = ?i',
            $id
          );
          $result = $this->db->fetch_object_array($sql);
          // The object doesn't exist in the DB
          if (! isset($result[0]->incident_id)) return false;
          $r = $result[0];
          $this->set_id($r->incident_id);
          $this->set_title($r->indident_title);
          $this->set_action_id($r->action_id);
          $this->set_action_discovery_timeframe($r->action_to_discovery_timeframe_id);
          $this->set_agent_id($r->agent_id);
          $this->set_asset_id($r->asset_id);
          $this->set_asset_loss_magnitude($r->asset_loss_magnitude_id);
          $this->set_authenticity_loss($r->authenticity_loss);
          $this->set_availability_loss_timeframe($r->availability_loss_timeframe_id);
          $this->set_confidential_data($r->confidential_data);
          $this->set_correction_recommended($r->correction_recommended);
          $this->set_discovery_evidence_sources($r->discovery_evidence_sources);
          $this->set_discovery($r->discovery_id);
          $this->set_discovery_metrics($r->discovery_metrics);
          $this->set_discovery_to_containment_timeframe($r->discovery_to_containment_timeframe_id);
          $this->set_disruption_magnitude($r->disruption_magnitude_id);
          $this->set_hindsight($r->hindsight);
          $this->set_impact_magnitude($r->impact_magnitude_id);
          $this->set_integrity_loss($r->integrity_loss);
          $this->set_month($r->month);
          $this->set_response_cost_magnitude($r->response_cost_magnitude_id);
          $this->set_utility_loss($r->utility_loss);
          $this->set_year($r->year);
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
          'DELETE FROM incident WHERE incident_id = \'?i\'',
          $this->get_id()
        );
        $retval = $this->db->iud_sql($sql);
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
    // Not used to to complexity of incident template
  }

  /**
   *  This function directly supports the Collection class.
   *
   * @return String SQL select string
   */
  public function get_collection_definition($filter = '', $orderby = '') {
    $query_args = array();
    $sql = 'SELECT i.incident_id FROM incident i WHERE i.incident_id > 0';
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
      $sql .= ' ORDER BY i.incident_year, i.incident_month';
    }
    return $sql;
  }

  /**
   * Get the displays for the default details template
   * 
   * @return Array Dispalays for default template
   */
  public function get_displays() {
    return array('Title'=>'get_title',
                'Year'=>'get_year',
                'Month'=>'get_month');
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
     * Return the Action object for this Incident
     */
     public function get_action() {
     	if (isset($this->action)) {
     		require_once('class.IRAction.php');
            return new IRAction($this->action);
     	}
        else {
        	return false;
        }
     }
     
     public function get_action_id() {
     	return intval($this->action);
     }
     
     public function get_action_to_discovery_timeframe_id() {
     	if (isset($this->action_to_discovery_timeframe_id)) {
     		return $this->action_to_discovery_timeframe_id;
     	}
        else {
        	return false;
        }
     }
     
     public function get_action_to_discovery_timeframe_readable() {
     	if (isset($this->action_to_discovery_timeframe_id)) {
            return $this->get_timeframe_readable($this->action_to_discovery_timeframe_id);
        }
        else {
            return false;
        }
     }
     
     public function get_action_id() {
     	return intval($this->action_id);
     }
     public function get_action() {
     	return (is_a($this->action, 'Action')) ? $this->action : false;
     }
     
     public function get_agent() {
     	if (isset($this->agent_id)) {
     		require_once('class.IRAgent.php');
            return new Agent($this->agent_id);
     	}
        else {
        	return false;
        }
     }
     
     public function get_agent_id() {
     	return intval($this->agent_id);
     }
     
     public function get_asset() {
        if (isset($this->asset)) {
            require_once('class.IRAsset.php');
            return new Agent($this->asset);
        }
        else {
            return false;
        }
     }
     
     public function get_asset_id() {
     	return intval($this->asset_id);
     }
     public function get_asset() {
     	return (is_a($this->asset, 'Asset')) ? $this->asset : false;
     }
     
     public function get_asset_loss_mangitude() {
     	return $this->asset_loss_magnitude_id;
     }
     
     public function get_asset_loss_magnitude_readable() {
     	return $this->get_magnitude_readable($this->get_asset_loss_magnitude());
     }
     
     /**
      * Description of the authenticity loss, in HTML display safe format
      * 
      * @return String Display safe description of authenticity loss
      */
     public function get_authenticity_loss() {
     	return htmlspecialchars($this->authenticity_loss);
     }
     
     public function get_availability_loss_timeframe() {
     	return $this->availability_loss_timeframe_id;
     }
     
     public function get_availability_loss_timeframe_readable() {
     	return $this->get_timeframe_readable($this->get_availability_loss_timeframe());
     }
     
     public function get_confidential_data() {
     	return (bool) $this->confidential_data;
     }
     
     public function get_correction_recommended() {
     	return htmlspecialchars($this->correction_recommendated);
     }
     
     public function get_discovery_evidence_sources() {
        return htmlspecialchars($this->discovery_evidence_sources);
     }
     
     public function get_discovery() {
        require_once('class.IRDiscovery.php');
     	return new IRDiscovery($this->discovery_id);
     }
     
     public function get_discovery_metrics() {
        return htmlspecialchars($this->discovery_metrics);
     }
     
     public function get_discovery_to_containment_timeframe() {
        return $this->discovery_to_containments_timeframe_id;
     }
     
     public function get_discovery_to_containments_timeframe_readable() {
        return $this->get_timeframe_readable($this->get_discovery_to_containment_timeframe());
     }
     
     public function get_disruption_mangitude() {
        return $this->disruption_magnitude_id;
     }
     
     public function get_disruption_magnitude_readable() {
        return $this->get_magnitude_readable($this->get_disruption_magnitude());
     }
     
     public function get_hindsight() {
        return htmlspecialchars($this->hindsight);
     }
     
     public function get_impact_mangitude() {
        return $this->impact_magnitude_id;
     }
     
     public function get_impact_magnitude_readable() {
        return $this->get_magnitude_readable($this->get_impact_magnitude());
     }
     
     public function get_integrity_loss() {
        return htmlspecialchars($this->integrity_loss);
     }
     
     public function get_month() {
     	return intval($this->month);
     }
     
     public function get_response_cost_mangitude() {
        return $this->response_cost_magnitude_id;
     }
     
     public function get_response_cost_magnitude_readable() {
        return $this->get_magnitude_readable($this->get_response_cost_magnitude());
     }
     
     public function get_utility_loss() {
        return htmlspecialchars($this->utility_loss);
     }
     
     public function get_year() {
        return intval($this->year);
     }
     
     private function get_magnitude_readable($id) {
     	$sql = array('SELECT magnitude_name ' .
                    'FROM incident_magnitude ' .
                    'WHERE magnitude_id = ?i',
                    $id);
        $result = $this->db->fetch_object_array($sql);
        if ($result) return $result[0]->magnitude_name;
        else return false;
     }
     
     private function get_timeframe_readable($id) {
     	$sql = array('SELECT timeframe_duration ' .
                    'FROM incident_timeframe ' .
                    'WHERE timeframe_id = ?i',
                    $id);
        $result = $this->db->fetch_object_array($sql);
        if ($result) return $result[0]->timeframe_duration;
        else return false;
     }

  /**
   * The HTML safe title of the Incident
   * 
   * @access public
   * @return String The HTML display safe name of the Incident.
   */
    public function get_title() {
        return htmlspecialchars($this->title);
    }

  /**
   * Persist the Incident to the data layer
   * 
   * @access public
   * @return Boolean True if everything worked, FALSE on error.
   */
    public function save() {
      $retval = FALSE;
      if ($this->id > 0 ) {
        // Update an existing incident
        $sql = array(
          'UPDATE incident SET ' .
            'action_id = ?i, ' .
            'agent_id = ?i, ' .
            'asset_id = ?i, ' .
            'confidential_data = ?b, ' .
            'integrity_loss = \'?s\', ' .
            'asset_loss_magnitude_id = ?i, ' .
            'authenticity_loss = \'?s\', ' .
            'action_to_discovery_timeframe_id = ?i, ' .
            'availability_loss_timeframe_id = ?i, ' .
            'correction_recommended = \'?s\', ' .
            'discovery_evidence_sources = \'?s\', ' .
            'discovery_id = ?i, ' .
            'discovery_metrics = \'?s\', ' .
            'discovery_to_containment_timeframe_id = ?i, ' .
            'disruption_magnitude_id = ?i, ' .
            '2020_hindsight = \'?s\', ' .
            'impact_magnitude_id = ?i, ' .
            'month = ?i,' .
            'response_cost_magnitude_id = ?i, ' .
            'title = \'?s\', ' .
            'utility_loss = \'?s\', ' .
            'year = ?i ' .
            'WHERE tag_id = \'?i\'',
          $this->get_action_id(),
          $this->get_agent_id(),
          $this->get_asset_id(),
          $this->get_confidential_data(),
          $this->get_integrity_loss(),
          $this->get_id()
        );
        $retval = $this->db->iud_sql($sql);
      }
      else {
        $sql = array(
        'INSERT INTO tag SET tag_name = \'?s\'',
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
    
    public function set_action($action) {
    	if (is_a($action, 'Action')) $this->action = $action;
    	else return false;
    	return true;
    }
    
    public function set_action_id($id) {
    	$this->action_id = intval($id);
    }
    
    public function set_agent($agent) {
    	if (is_a($agent, 'Agent')) $this->agent = $agent;
    	else return false;
    	return true;
    }
    
    public function set_agent_id($id) {
    	$this->agent_id = intval($id);
    }
    
    public function set_asset($asset) {
    	if (is_a($asset, 'Asset')) $this->asset = $asset;
    	else return false;
    	return true;
    }
    
    public function set_asset_id($id) {
    	$this->asset_id = intval($id);
    }
    
    public function set_authenticity_loss($text) {
    	$this->authenticity_loss = $text;
    }
    
    public function set_confidential_data($cdata) {
    	$this->confidential_data = intval((bool) $cdata);
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
    
    public function set_integrity_loss($text) {
    	$this->integrity_loss = $text;
    }
    
	public function set_month($month) {
		$month = intval($month);
		if ($month < 1 || $month > 12) return false;
		else $this->month = $month;
	}

  /**
   * Set the title of the Incident
   * 
   * @access public
   * @param String The title of the incident
   */
    public function set_title($title) {
      if ($title == '') $title = 'Untitled Incident';
      $this->title = $title;
    }
    
	public function set_year($year) {
		$this->year = intval($year);
	}

} /* end of class Incident */

?>