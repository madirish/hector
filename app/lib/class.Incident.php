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
     private $authenticity_loss = null; // TEXT,
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
            'SELECT *,2020_hindsight AS hindsight FROM incident WHERE incident_id = ?i',
            $id
          );
          $result = $this->db->fetch_object_array($sql);
          // The object doesn't exist in the DB
          if (! isset($result[0]->incident_id)) return false;
          $r = $result[0];
          $this->set_id($r->incident_id);
          $this->set_title($r->incident_title);
          $this->set_action_id($r->action_id);
          $this->set_action_to_discovery_timeframe_id($r->action_to_discovery_timeframe_id);
          $this->set_agent_id($r->agent_id);
          $this->set_asset_id($r->asset_id);
          $this->set_asset_loss_magnitude_id($r->asset_loss_magnitude_id);
          $this->set_authenticity_loss($r->authenticity_loss);
          $this->set_availability_loss_timeframe_id($r->availability_loss_timeframe_id);
          $this->set_confidential_data($r->confidential_data);
          $this->set_correction_recommended($r->correction_recommended);
          $this->set_discovery_evidence_sources($r->discovery_evidence_sources);
          $this->set_discovery_id($r->discovery_id);
          $this->set_discovery_metrics($r->discovery_metrics);
          $this->set_discovery_to_containment_timeframe_id($r->discovery_to_containment_timeframe_id);
          $this->set_disruption_magnitude_id($r->disruption_magnitude_id);
          $this->set_hindsight($r->hindsight);
          $this->set_impact_magnitude_id($r->impact_magnitude_id);
          $this->set_integrity_loss($r->integrity_loss);
          $this->set_month($r->incident_month);
          $this->set_response_cost_magnitude_id($r->response_cost_magnitude_id);
          $this->set_utility_loss($r->utility_loss);
          $this->set_year($r->incident_year);
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
      $sql .= ' ORDER BY i.incident_year, i.incident_month DESC';
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
     
     public function get_action_to_discovery_timeframe_id() {
     	if (isset($this->action_to_discovery_timeframe_id)) {
     		return $this->action_to_discovery_timeframe_id;
     	}
        else {
        	return false;
        }
     }
     
     public function get_action_to_discovery_timeframe_friendly() {
        include_once('class.IRTimeframe.php');
        $tframe = new IRTimeframe($this->get_action_to_discovery_timeframe_id());
        return $tframe->get_duration();
     }
     
     public function get_action_id() {
     	return intval($this->action_id);
     }
     public function get_action() {
     	require_once('class.IRAction.php');
        return new IRAction($this->get_action_id());
     }
     
     public function get_agent() {
     	if (isset($this->agent_id)) {
     		require_once('class.IRAgent.php');
            return new IRAgent($this->agent_id);
     	}
        else {
        	return false;
        }
     }
     
     public function get_agent_id() {
     	return intval($this->agent_id);
     }
     
     public function get_asset() {
     	require_once('class.IRAsset.php');
        return new IRAsset($this->get_asset_id());
     }
     
     public function get_asset_id() {
     	return intval($this->asset_id);
     }
     
     public function get_asset_loss_magnitude_id() {
     	return $this->asset_loss_magnitude_id;
     }
     
     public function get_asset_loss_magnitude_friendly() {
     	require_once('class.IRMagnitude.php');
        if ($ret = new IRMagnitude($this->get_asset_loss_magnitude_id())) {
        	return $ret->get_name();
        }
        return false;
     }
     
     /**
      * Description of the authenticity loss, in HTML display safe format
      * 
      * @return String Display safe description of authenticity loss
      */
     public function get_authenticity_loss() {
     	return htmlspecialchars($this->authenticity_loss);
     }
     
     public function get_availability_loss_timeframe_id() {
     	return $this->availability_loss_timeframe_id;
     }
     
     public function get_availability_loss_timeframe_friendly() {
     	include_once('class.IRTimeframe.php');
        $tframe = new IRTimeframe($this->get_availability_loss_timeframe_id());
        return $tframe->get_duration();
     }
     
     public function get_confidential_data() {
     	return (bool) $this->confidential_data;
     }
     
     public function get_correction_recommended() {
     	return htmlspecialchars($this->correction_recommended);
     }
     
     public function get_discovery_evidence_sources() {
        return htmlspecialchars($this->discovery_evidence_sources);
     }
     
     public function get_discovery() {
        require_once('class.IRDiscovery.php');
     	return new IRDiscovery($this->discovery_id);
     }
     
     public function get_discovery_id() {
     	return $this->discovery_id;
     }
     
     public function get_discovery_method_friendly() {
     	require_once('class.IRDiscovery.php');
        $disco = new IRDiscovery($this->get_discovery_id());
        return $disco->get_method();
     }
     
     public function get_discovery_metrics() {
        return htmlspecialchars($this->discovery_metrics);
     }
     
     public function get_discovery_to_containment_timeframe_id() {
        return $this->discovery_to_containment_timeframe_id;
     }
     
     public function get_discovery_to_containment_timeframe_friendly() {
        include_once('class.IRTimeframe.php');
        $tframe = new IRTimeframe($this->get_discovery_to_containment_timeframe_id());
        return $tframe->get_duration();
     }
     
     public function get_disruption_magnitude_id() {
        return $this->disruption_magnitude_id;
     }
     
     public function get_disruption_magnitude_friendly() {
        require_once('class.IRMagnitude.php');
        if ($ret = new IRMagnitude($this->get_disruption_magnitude_id())) {
            return $ret->get_name();
        }
        return false;
     }
     
     public function get_hindsight() {
        return htmlspecialchars($this->hindsight);
     }
     
     public function get_impact_magnitude_id() {
        return $this->impact_magnitude_id;
     }
     
     public function get_impact_magnitude_friendly() {
        require_once('class.IRMagnitude.php');
        if ($ret = new IRMagnitude($this->get_impact_magnitude_id())) {
            return $ret->get_name();
        }
        return false;
     }
     
     public function get_integrity_loss() {
        return htmlspecialchars($this->integrity_loss);
     }
     
     public function get_month() {
     	return intval($this->month);
     }
     
     public function get_month_friendly() {
     	$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $month = $this->get_month() - 1;
        if ($month >= 0) return $months[$month];
        else return false;
     }
     
     public function get_response_cost_magnitude_id() {
        return $this->response_cost_magnitude_id;
     }
     
     public function get_response_cost_magnitude_friendly() {
        require_once('class.IRMagnitude.php');
        if ($ret = new IRMagnitude($this->get_response_cost_magnitude_id())) {
            return $ret->get_name();
        }
        return false;
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
      // All non-string fields are required
      if ($this->action_id == null ||
          $this->agent_id == null ||
          $this->asset_id == null ||
          $this->asset_loss_magnitude_id == null ||
          $this->availability_loss_timeframe_id == null ||
          $this->discovery_id == null ||
          $this->discovery_to_containment_timeframe_id == null ||
          $this->disruption_magnitude_id == null ||
          $this->impact_magnitude_id == null ||
          $this->response_cost_magnitude_id == null ||
          $this->title == null ||
          $this->month == null ||
          $this->year == null) {
        return false;
      }
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
            'authenticity_loss = \'?s\', ' .
            'utility_loss = \'?s\', ' .
            'availability_loss_timeframe_id = ?i, ' .
            'action_to_discovery_timeframe_id = ?i, ' .
            'discovery_to_containment_timeframe_id = ?i, ' .
            'discovery_id = ?i, ' .
            'discovery_evidence_sources = \'?s\', ' .
            'discovery_metrics = \'?s\', ' .
            'asset_loss_magnitude_id = ?i, ' .
            'disruption_magnitude_id = ?i, ' .
            'response_cost_magnitude_id = ?i, ' .
            'impact_magnitude_id = ?i, ' .
            '2020_hindsight = \'?s\', ' .
            'correction_recommended = \'?s\', ' .
            'incident_title = \'?s\', ' .
            'incident_month = ?i,' .
            'incident_year = ?i ' .
            'WHERE incident_id = \'?i\'',
          $this->get_action_id(),
          $this->get_agent_id(),
          $this->get_asset_id(),
          $this->get_confidential_data(),
          $this->get_integrity_loss(),
          $this->get_authenticity_loss(),
          $this->get_utility_loss(),
          $this->get_availability_loss_timeframe_id(),
          $this->get_action_to_discovery_timeframe_id(),
          $this->get_discovery_to_containment_timeframe_id(),
          $this->get_discovery_id(),
          $this->get_discovery_evidence_sources(),
          $this->get_discovery_metrics(),
          $this->get_asset_loss_magnitude_id(),
          $this->get_disruption_magnitude_id(),
          $this->get_response_cost_magnitude_id(),
          $this->get_impact_magnitude_id(),
          $this->get_hindsight(),
          $this->get_correction_recommended(),
          $this->get_title(),
          $this->get_month(),
          $this->get_year(),
          $this->get_id()
        );
        $retval = $this->db->iud_sql($sql);
      }
      else {
        $sql = array(
        'INSERT INTO incident SET action_id = ?i, ' .
            'agent_id = ?i, ' .
            'asset_id = ?i, ' .
            'confidential_data = ?b, ' .
            'integrity_loss = \'?s\', ' .
            'authenticity_loss = \'?s\', ' .
            'utility_loss = \'?s\', ' .
            'availability_loss_timeframe_id = ?i, ' .
            'action_to_discovery_timeframe_id = ?i, ' .
            'discovery_to_containment_timeframe_id = ?i, ' .
            'discovery_id = ?i, ' .
            'discovery_evidence_sources = \'?s\', ' .
            'discovery_metrics = \'?s\', ' .
            'asset_loss_magnitude_id = ?i, ' .
            'disruption_magnitude_id = ?i, ' .
            'response_cost_magnitude_id = ?i, ' .
            'impact_magnitude_id = ?i, ' .
            '2020_hindsight = \'?s\', ' .
            'correction_recommended = \'?s\', ' .
            'incident_title = \'?s\', ' .
            'incident_month = ?i,' .
            'incident_year = ?i ',
          $this->get_action_id(),
          $this->get_agent_id(),
          $this->get_asset_id(),
          $this->get_confidential_data(),
          $this->get_integrity_loss(),
          $this->get_authenticity_loss(),
          $this->get_utility_loss(),
          $this->get_availability_loss_timeframe_id(),
          $this->get_action_to_discovery_timeframe_id(),
          $this->get_discovery_to_containment_timeframe_id(),
          $this->get_discovery_id(),
          $this->get_discovery_evidence_sources(),
          $this->get_discovery_metrics(),
          $this->get_asset_loss_magnitude_id(),
          $this->get_disruption_magnitude_id(),
          $this->get_response_cost_magnitude_id(),
          $this->get_impact_magnitude_id(),
          $this->get_hindsight(),
          $this->get_correction_recommended(),
          $this->get_title(),
          $this->get_month(),
          $this->get_year()
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
    
    public function set_action_to_discovery_timeframe_id($id) {
        // Validate the $id using the IRTimeframe constructor
        require_once('class.IRTimeframe.php');
        $tframe = new IRTimeframe($id);
        if ($tframe->get_id() > 0) {
            $this->action_to_discovery_timeframe_id = $tframe->get_id();
            return true;
        }
        return false;
    }
    
    public function set_action_id($id) {
        require_once('class.IRAction.php');
        $action = new IRAction($id);
        if ($action->get_id() > 0) {
        	$this->action_id = $action->get_id();
            return true;
        }
        return false;
    }
    
    public function set_agent($agent) {
    	if (is_a($agent, 'Agent')) $this->agent = $agent;
    	else return false;
    	return true;
    }
    
    public function set_agent_id($id) {
        require_once('class.IRAgent.php');
        $agent = new IRAgent($id);
        if ($agent->get_id() > 0) {
            $this->agent_id = $agent->get_id();
            return true;
        }
        return false;
    }
    
    public function set_asset($asset) {
    	if (is_a($asset, 'Asset')) $this->asset = $asset;
    	else return false;
    	return true;
    }
    
    public function set_asset_id($id) {
        require_once('class.IRAsset.php');
        $asset = new IRAsset($id);
        if ($asset->get_id() > 0) {
        	$this->asset_id = $asset->get_id();
            return true;
        }
        return false;
    }
    
    public function set_authenticity_loss($text) {
    	$this->authenticity_loss = $text;
    }
    
    public function set_availability_loss_timeframe_id($id) {
        // Validate the $id using the IRTimeframe constructor
        require_once('class.IRTimeframe.php');
        $tframe = new IRTimeframe($id);
        if ($tframe->get_id() > 0) {
        	$this->availability_loss_timeframe_id = $tframe->get_id();
            return true;
        }
        return false;
    }
    
    public function set_asset_loss_magnitude_id($id) {
    	require_once('class.IRMagnitude.php');
        $mag = new IRMagnitude($id);
        if ($mag->get_id() > 0) {
        	$this->asset_loss_magnitude_id = $mag->get_id();
            return true;
        }
        return false;
    }
    
    public function set_confidential_data($cdata) {
    	$this->confidential_data = intval((bool) $cdata);
    }
    
    public function set_correction_recommended($text) {
    	$this->correction_recommended = $text;
    }
    
    public function set_discovery_id($id) {
        // Validate the $id using the IRDiscovery constructor
    	require_once('class.IRDiscovery.php');
        $discovery = new IRDiscovery($id);
        if ($discovery->get_id() > 0) {
        	$this->discovery_id = $discovery->get_id();
            return true;
        }
        return false;
    }
    
    public function set_discovery_to_containment_timeframe_id($id) {
        // Validate the $id using the IRTimeframe constructor
        require_once('class.IRTimeframe.php');
        $tframe = new IRTimeframe($id);
        if ($tframe->get_id() > 0) {
            $this->discovery_to_containment_timeframe_id = $tframe->get_id();
            return true;
        }
        return false;
    }
    
    public function set_discovery_evidence_sources($text) {
    	$this->discovery_evidence_sources = $text;
    }
    
    public function set_discovery_metrics($text) {
    	$this->discovery_metrics = $text;
    }
    
    public function set_disruption_magnitude_id($id) {
        require_once('class.IRMagnitude.php');
        $mag = new IRMagnitude($id);
        if ($mag->get_id() > 0) {
        	$this->disruption_magnitude_id = $mag->get_id();
            return true;
        }
        return false;
    }
    
    public function set_hindsight($text) {
    	$this->hindsight = $text;
    }
    
    public function set_utility_loss($text) {
        $this->utility_loss = $text;
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
    
    public function set_impact_magnitude_id($id) {
        require_once('class.IRMagnitude.php');
        $mag = new IRMagnitude($id);
        if ($mag->get_id() > 0) {
        	$this->impact_magnitude_id = $mag->get_id();
            return true;
        }
        return false;
    }
    
    public function set_integrity_loss($text) {
    	$this->integrity_loss = $text;
    }
    
	public function set_month($month) {
		$month = intval($month);
		if ($month < 1 || $month > 12) return false;
		else $this->month = $month;
        return true;
	}
    
    public function set_response_cost_magnitude_id($id) {
        require_once('class.IRMagnitude.php');
        $mag = new IRMagnitude($id);
        if ($mag->get_id() > 0) {
        	$this->response_cost_magnitude_id = $mag->get_id();
            return true;
        }
        return false;
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