<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');



class Nessus
{
	
	/**
	 * Url of the request
	 * 
	 * @access private
	 * @var String The url of the api request
	 */
    private $url;
    
    /**
	 * Port used on the server
	 * 
	 * @access private
	 * @var String The server port
	 */
    private $port;
    
    /**
	 * Username of the request
	 * 
	 * @access private
	 * @var String The username used to authenticate
	 */
    private $username;
    
    /**
	 * Password for the request
	 * 
	 * @access private
	 * @var String The Password used to authenticate
	 */
    private $password;
    
    /**
	 * Session Token
	 * 
	 * @access private
	 * @var String The current session token
	 */
    private $token;
    
    /**
	 * Sequence number for each request
	 * 
	 * @access private
	 * @var String The sequence number
	 */
    private $sequence;
	
	/**
	 * Http Status code
	 * 
	 * @access private
	 * @var String The Status Code of the request
	 */
    private $http_status;
    
    /**
	 * Call of the request
	 * 
	 * @access private
	 * @var String The call of the api request
	 */
    private $call;
    

   /**
	* Instantiate the instance
	*
	* @param string $url The host to which we should connect.
	* @param string $port The port to which we should connect.
	* @param string $username The username
	* @param string $password The that would be used.
	*
	* @return nothing
	*/
    public function __construct($url, $port, $username, $password) {

        // Check that we have a valid URL here.
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid URL for NessusInterface Object", 1);
        }

        // Check that we have a valid port here.
        if (!is_numeric($port) || ( $port != 8834)) {
            throw new Exception("Invalid port for NessusInterface Object", 1);
        }

        // Prepare the full url
        $this->url = rtrim($url, "/") . ":" . $port;
        $this->username = $username;
        $this->password = $password;

        // Perform the login and set the token that will be used.
        $this->login();
    }

   /**
	* Class deconstructor used once all references to this Class is cleared. We want to log out cleanly.
	*
	* @return void
	*/
    public function __destruct() {

        $this->logout();
    }

   /**
	* Check a cURL response and its headers to ensure that it was successfull.
	*
	* @param string $ch The cURL connection object.
	* @param string $result The result from a cURL request
	*
	* @return nothing
	*/
    private function checkResponse($ch, $result) {

        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->http_status == 403) {

            throw new Exception("Unauthorized Request to " . $this->call, 1);
        }

        if ($this->http_status <> 200) {

            throw new Exception("Failed/Timedout API Request to " . $this->call, 1);
        }

        // Parse the XML to check the status and read the error if required
        $xml = new SimpleXMLElement($result);
        if ($xml->status <> "OK") {
            throw new Exception("Error Processing Request. Error was: " . $xml->contents, 1);
        }
    }

   /**
	* Generate a random sequence number betwee 1 and 65535. This is used for API call synchronization checks.
	*
	* @return nothing
	*/
    private function setSequence() {

        $this->sequence = rand(1, 65535);
    }

   /**
	* Check that the returned sequence number matched the sequence that was sent.
	*
	* @param string $sequence The received sequence number from the API return
	*
	* @return nothing
	*/
    private function checkSequence($sequence) {

        if ($sequence <> $this->sequence) {

            throw new Exception(
                "Out of sequence request calling " . $this->call . ". Got #$sequence instead of #" . $this->sequence,
                1
            );
        }
    }

   /**
	* Log API requests to the Applications General Log
	*
	* @return nothing
	*/
    private function logRequest() {
        // This can be configured to do anything you like really.
        return null;
    }

   /**
	* Check that the returned sequence number matched the sequence that was sent.
	*
	* @param array $fields An array with arguements that accompany the endpoint
	* @param string $endpoint The API endpoint that should be called
	*
	* @return XML containing the endpoint response
	*/
    private function callApi($fields, $endpoint) {

        //Set RPC funtion to URL
        $this->call = $this->url . $endpoint;

        //set POST variables
        $fields_string = null;

        //url-ify the data for the POST
        foreach ($fields as $key=>$value) {
            $fields_string .= $key."=".$value."&";
        }
        rtrim($fields_string, "&");

        // Log the request
        $this->logRequest();

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->call);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);

        // Check what we got back
        $this->checkResponse($ch, $result);

        //close connection
        curl_close($ch);

        // Parse the XML and populate the Object
        $xml = new SimpleXMLElement($result);

        // Check the response Sequence Number
        $this->checkSequence((string)$xml->seq);

        // Return the response
        return $xml;
    }

   /**
	* Login to the Nessus Server preserving the token in this->token
	*
	* @return nothing
	*/
    private function login() {

        // Set a new Sequence Number
        $this->setSequence();

        //set POST variables
        $fields = array(
            "login" =>urlencode($this->username),
            "password" =>urlencode($this->password),
            "seq" =>urlencode($this->sequence)
        );

        // Set the API Endpoint we will call
        $endpoint = "/login";

        // Do the Request
        $xml = $this->callApi($fields, $endpoint);

        // Set the session token
        $this->token = (string)$xml->contents->token;
    }

   /**
	* Log out of the scanner, effectively destroying the token
	*
	* @return nothing
	*/
    private function logout() {

        // Set a new the Sequence
        $this->setSequence();

        //set POST variables
        $fields = array(
            "token" =>urlencode($this->token),
            "seq" =>urlencode($this->sequence)
        );

        // Set the API Endpoint we will call
        $endpoint = "/logout";

        // Do the Request
        $xml = $this->callApi($fields, $endpoint);

        // Unset the session token
        $this->token = null;
    }
    
   /**
	* Start a new scan on the nessus server.
	*
	* @return An array containing scan uuid values.
	*/
    public function scan_new($target, $policy_id, $scan_name) {

        // Set a new the Sequence
        $this->setSequence();

        // Set POST variables
        $fields = array(
        	"target" => urlencode($target),
        	"policy_id" => urlencode($policy_id),
        	"scan_name" => urlencode($scan_name),
            "token" =>urlencode($this->token),
            "seq" =>urlencode($this->sequence)
        );

        // Set the API Endpoint we will call
        $endpoint = "/scan/new";

        // Do the Request
        $xml = $this->callApi($fields, $endpoint);

        $values= array (
            "uuid" => (string)$xml->contents->scan->uuid
        );

        // Return what we got
        return($values);
    }
    
   /**
	* Retreive a list of all the policies on the scanner.
	*
	* @return An array containing a list of the policies
	*/
    public function policy_list() {

        // Set a new the Sequence
        $this->setSequence();

        //set POST variables
        $fields = array(
            "token" => urlencode($this->token),
            "seq" =>urlencode($this->sequence)
        );

        // Set the API Endpoint we will call
        $endpoint = "/policy/list";

        // Do the Request
        $xml = $this->callApi($fields, $endpoint);
        
        // Prepare the return array
        $values = array ();
        foreach ($xml->contents->policies->policy as $response) {
	    	$values["policies"][(string)$response->policyID]["policyName"] = (string)$response->policyName;
        }

        // Return what we get
        return($values);
    }
    
   /**
	* Retreive a list of all the reports on the scanner.
	*
	* @return An array containing the report list
	*/
    public function report_list() {

        // Set a new the Sequence
        $this->setSequence();

        //set POST variables
        $fields = array(
            "token" => urlencode($this->token),
            "seq" =>urlencode($this->sequence)
        );

        // Set the API Endpoint we will call
        $endpoint = "/report/list";

        // Do the Request
        $xml = $this->callApi($fields, $endpoint);

        // Prepare the return array
        $values = array ();
        foreach ($xml->contents->reports->report as $report) {
	    	$values["reports"][(string)$report->readableName]["name"] = (string)$report->name;
            $values["reports"][(string)$report->readableName]["status"] = (string)$report->status;
            $values["reports"][(string)$report->readableName]["timestamp"] = (string)$report->timestamp;
        }

        // Return what we get
        return($values);
    }
    
   /**
	* Retreive a list of all the hosts scanned
	*
	* @return An array containing the list of hosts
	*/
    public function report_hosts($uuid) {
 
        // Set a new the Sequence
        $this->setSequence();

        //set POST variables
        $fields = array(
        	"report" => urlencode($uuid),
            "token" => urlencode($this->token),
            "seq" =>urlencode($this->sequence)
        );
		
        // Set the API Endpoint we will call
        $endpoint = "/report2/hosts";

        // Do the Request
        $xml = $this->callApi($fields, $endpoint);

        // Prepare the return array
        $values = array ();
        foreach ($xml->contents->hostList->host as $response) {
	    	$values["hostList"]["hostname"] = (string)$response->hostname;
        }

        // Return what we get
        return($values);
    }
    
    /**
	* Retreive a list of all the ports scanned
	*
	* @return An array containing the list of all ports and protocols
	*/
	public function report_ports($uuid, $hostname) {
		
		// Set a new Sequence
		$this->setSequence();
		
		// Set POST variables
		$fields = array(
			"report" => urlencode($uuid),
			"hostname" => urlencode($hostname),
			"seq" => urlencode($this->sequence),
			"token" => urlencode($this->token)
		);

		// Set the API Endpoint we will call
		$endpoint = "/report/ports";
		
		// Do the Request
		$xml = $this->callApi($fields, $endpoint);
		
		$values = array();
		//$values1 =array();
		foreach ($xml->contents->portList->port as $key => $val) {
			$values[] = array(
				"port" => (string)$val->portNum,
				"protocol" => (string)$val->protocol
			);
		}
	
		// Return what we got
		return($values);
	}
    
    /**
	* Retreive a list of each scan details
	*
	* @return An array containing the scan details
	*/
	public function report_details($uuid, $hostname, $port, $protocol) {
		
		// Set a new Sequence
		$this->setSequence();
		
		// Set POST variables
		$fields = array(
			"report" => urlencode($uuid),
			"hostname" => urlencode($hostname),
			"port" => urlencode($port),
			"protocol" => urlencode($protocol),
			"seq" => urlencode($this->sequence),
			"token" => urlencode($this->token)
		);
		
		// Set the API Endpoint we will call
		$endpoint = "/report/details";
		
		// Do the Request
		$xml = $this->callApi($fields, $endpoint);
		
		$values = array ();
		foreach ($xml->contents->portDetails->ReportItem as $response) {
			$values["portDetails"][(string)$response->pluginID]["pluginName"] = (string)$response->pluginName;
			$values["portDetails"][(string)$response->pluginID]["port"] = (string)$response->port;
			$values["portDetails"][(string)$response->pluginID]["severity"] = (string)$response->severity;
			$values["portDetails"][(string)$response->pluginID]["data"]["solution"] = (string)$response->data->solution;
			$values["portDetails"][(string)$response->pluginID]["data"]["description"] = (string)$response->data->description;
			$values["portDetails"][(string)$response->pluginID]["data"]["risk_factor"] = (string)$response->data->risk_factor;
			$values["portDetails"][(string)$response->pluginID]["data"]["synopsis"] = (string)$response->data->synopsis;
			$values["portDetails"][(string)$response->pluginID]["data"]["plugin_output"] = (string)$response->data->plugin_output;
		}
		
		// Return what we got
		return($values);
	}
	
	// Must be a Nessus Administrator to run this
	public function plugins_update() {
		
		// Set a new Sequence
		$this->setSequence();
		
		// Set POST Variables
		$fields = array(
			"seq" => urlencode($this->sequence),
			"token" => urlencode($this->token)
		);
		
		// Set the API Endpoint we will call
		$endpoint = "/plugins/process";
		
		// Do the Request
		$xml = $this->callApi($fields, $endpoint);
		
		$values = array (
			"Status Request" => $xml->contents->plugins_processing
		);
		
		// Return what we got
		return($values);
	}
	
	public function new_scan_template($template_name, $policy_id, $target, $start_time, $rrules) {
		
		// Set a new Sequence
		$this->setSequence();
		
		// Set POST Variables
		$fields = array (
			"template_name" => urlencode($template_name),
			"policy_id" => urlencode($policy_id),
			"target" => urlencode($target),
			"startTime" => urlencode($start_time),
			"rRules" => urlencode($rrules),
			"seq" => urlencode($this->sequence),
			"token" => urlencode($this->token)
		);
		
		// Set the API Endpoint we will call
		$endpoint = "/scan/template/new";
		
		// Do the Request
		$xml = $this->callApi($fields, $endpoint);
		
		$values = array (
			"status" => $xml->status,
			"template name" => $xml->contents->name
		);
		
		// Return what we got
		return($values);
	}
	
	/**
	* Retreive a list of all the active scans
	*
	* @return An array containing the scan ids and statuses 
	*/
	public function launch_scan_template($template) {
		
		// Set a new Sequence
		$this->setSequence();
		
		// Set POST Variables
		$fields = array (
			"template" => urlencode($template),
			"seq" => urlencode($this->sequence),
			"token" => urlencode($this->token)
		);
		
		// Set the API Endpoint we will call
		$endpoint = "/scan/tmeplate/launch";
		
		// Do the Request
		$xml = $this->callApi($fields, $endpoint);
		
		$values = array (
			"status" => $xml->status,
			"uuid" => $xml->contents->uuid,
			"start time" => $xml->contents->start_time
		);
		
		// Return what we got
		return($values);
	}
	
	/**
	* Retreive a list of all the vuln information
	*
	* @return An array containing the cve's and osvdb's for each vuln
	*/
	public function report_plugin_details($uuid, $hostname, $port, $protocol, $severity, $plugin_id) {
		
		// Set a new Sequence
		$this->setSequence();
		
		// Set POST variables
		$fields = array(
			"report" => urlencode($uuid),
			"hostname" => urlencode($hostname),
			"port" => urlencode($port),
			"protocol" => urlencode($protocol),
			"severity" => urlencode($severity),
			"plugin_id" => urlencode($plugin_id),
			"seq" => urlencode($this->sequence),
			"token" => urlencode($this->token)
		);
		
		// Set the API Endpoint we will call
		$endpoint = "/report2/details/plugin";
		
		// Do the Request
		$xml = $this->callApi($fields, $endpoint);
		
		$values = array ();
		foreach ($xml->contents->portDetails->ReportItem as $response) {
			$values["portDetails"][(string)$response->pluginID]["pluginName"] = (string)$response->pluginName;
			$values["portDetails"][(string)$response->pluginID]["cve"] = (string)$response->data->cve;
			$values["portDetails"][(string)$response->pluginID]["osvdb"] = (string)$response->data->osvdb;
		}
		
		// Return what we got
		return($values);
	}

}
?>