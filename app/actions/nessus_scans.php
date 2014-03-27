<?php
require_once($approot . 'lib/class.Config.php');
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Log.php');
include_once($templates. 'admin_headers.tpl.php');
require_once($approot . 'lib/class.Form.php');
require_once($approot . 'lib/class.Nessus.php');

    $api = new Nessus(
        $__url = 'https://192.168.56.4',
        $__port = '8834',
        $__username = "",
        $__password = ""
    );

    $policy = $api->policy_list();
//    ///print_r($policy);
//    foreach ($policy as $keypol => $pol) {
//    	//print_r($pol);
//    	foreach ($pol as $keya => $valpol) {
//    		print_r($keya);
//    		print_r($valpol["policyName"]);
//    		echo "<br />";
//    	}
//    }

	echo "<br /><br /><br /><br />";

	$con = mysql_connect("192.168.56.2","","");
	if (!$con)
	  {
	  die('Could not connect: ' . mysql_error());
	  }

	mysql_select_db("hector", $con);

	$reports = $api->report_list();
	//print_r($reports["reports"]);
	foreach ($reports["reports"] as $response) {
		$uuid_values = array(
			"name" => $response["name"],
			"status" => $response["status"],
			"timestamp" => $response["timestamp"]
		);
	
		$hosts = $api->report_hosts($uuid_values["name"]);
		//print_r($hosts);
		foreach ($hosts as $keyh => $u) {
			//print_r($hosts);
			$host_uuid = array(
				"uuid" => $uuid_values["name"],
				"hostname" => $u["hostname"],
				"status" => $uuid_values["status"],
				"timestamp" => $uuid_values["timestamp"]
			);

			$ports = $api->report_ports($host_uuid["uuid"], $host_uuid["hostname"]);
			//print_r($ports);
			foreach ($ports as $key => $val) {
				//print_r($val);
				$port_values = array (
					"uuid" => $host_uuid["uuid"],
					"hostname" => $host_uuid["hostname"],
					"port" => $val["port"],
					"protocol" => $val["protocol"],
					"status" => $host_uuid["status"],
					"timestamp" => $host_uuid["timestamp"]
				);
			//print_r($port_values);
			
				$details = $api->report_details($port_values["uuid"], $port_values["hostname"], $port_values["port"], $port_values["protocol"]);
				foreach ($details["portDetails"] as $keyd => $d) {
					//print_r($details_values);
					$details_values = array(
						"uuid" => $port_values["uuid"],
						"hostname" => $port_values["hostname"],
						"port" => $port_values["port"],
						"protocol" => $port_values["protocol"],
						"status" => $port_values["status"],
						"timestamp" => $port_values["timestamp"],
						"plugin id" => $keyd,
						"plugin name" => $d["pluginName"],
						"severity" => $d["severity"],
						"solution" => $d["data"]["solution"],
						"description" => $d["data"]["description"],
						"risk factor" => $d["data"]["risk_factor"],
						"synopsis" => $d["data"]["synopsis"],
						"plugin output" => $d["data"]["plugin_output"]
					);
	
					if ($details_values['risk factor'] == "None") {
						$severity = "0";
						$plugins = $api->report_plugin_details($details_values['uuid'], $details_values['hostname'], $details_values['port'], $details_values['protocol'], $severity, $details_values['plugin id']);
					}
					else
						$plugins = $api->report_plugin_details($details_values['uuid'], $details_values['hostname'], $details_values['port'], $details_values['protocol'], $details_values['severity'], $details_values['plugin id']);
					
					foreach ($plugins["portDetails"] as $keyp => $plug) {
						$plugin_values = array(
							"uuid" => $details_values["uuid"],
							"hostname" => $details_values["hostname"],
							"port" => $details_values["port"],
							"protocol" => $details_values["protocol"],
							"status" => $details_values["status"],
							"timestamp" => $details_values["timestamp"],
							"plugin_id" => $details_values["plugin id"],
							"plugin_name" => $details_values["plugin name"],
							"severity" => $details_values["severity"],
							"solution" => $details_values["solution"],
							"description" => $details_values["description"],
							"risk_factor" => $details_values["risk factor"],
							"synopsis" => $details_values["synopsis"],
							"plugin_output" => $details_values["plugin output"],
							"cve" => $plug["cve"],
							"osvdb" => $plug["osvdb"]
						);
						
//						echo "<pre>";
//						print_r($plugin_values);
//						echo "</pre>";
//						
//						
//						$hostname =  mysql_real_escape_string($plugin_values["hostname"]);
//						$plugname =  mysql_real_escape_string($plugin_values["plugin_name"]);
//						$description = mysql_real_escape_string($plugin_values["description"]);
//						$cve =  mysql_real_escape_string($plugin_values["cve"]);
//						$osvdb =  mysql_real_escape_string($plugin_values["osvdb"]);
//						$output = mysql_real_escape_string($plugin_values["plugin_output"]);
//						$res = mysql_query("call nessus_import1('$hostname', '$plugname', '$description', '$cve', '$osvdb', '$output')");
//						if ($res === FALSE) {
//						    die(mysql_error());
//						}
					
					}
				}
			}
		}
	}
	
	$scan = $_POST['scanname'];
	$host = $_POST['hostname'];
	$policynes = -1;
	$api->scan_new($host, $policynes, $scan);
	include_once($templates. 'nessus.tpl.php');

?>
