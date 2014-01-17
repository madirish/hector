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
	* Retreive technical details about the scanner such as Server Version etc.
	*
	* @return An array containing the server details
	*/
    public function scan_new($target, $policyID) {

        // Set a new the Sequence
        $this->setSequence();
        $target = "coop.sas.upenn.edu";
		$policyID = -1;
        // Set POST variables
        $fields = array(
        	"target" => urlencode($target),
        	"policy_id" => urlencode($policyID),
        	"scan_name" => urlencode("Scan 5"),
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
	* Retreive a list of all the reports in the scanner.
	*
	* @return An array containing the report list
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
	    	//$values["policies"][(string)$response->policyID]["policyOwner"] = (string)$response->policyOwner;
        }

        // Return what we get
        return($values);
    }
    
   /**
	* Retreive a list of all the reports in the scanner.
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
	* Retreive a list of all the reports in the scanner.
	*
	* @return An array containing the report list
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
		
		$values = array ();
		foreach ($xml->contents->portList->port as $response) {
			$values["portList"][(string)$response->portNum]["protocol"] = (string)$response->protocol;
//			if ($response->portNum == $response->portNum) {
//				foreach ($response->portNum as $test){
//					$values = array(
//						"port" => $test,
//						"protocol" => $response->protocol
//					);
//					print_r($values);
//				}
//			}
		}
		
		// Return what we got
		return($values);
	}
    
	public function report_details($uuid, $hostname, $port, $protocol) {
		
		// Set a new Sequence
		$this->setSequence();
		
		//$vuln_port = "0";
		//$protocol = "tcp";
		
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
			//$values["portDetails"][(string)$response->pluginID]["data"]["plugin_version"] = (string)$response->data->plugin_version;
		}
		
		// Return what we got
		return($values);
	}
}
?>