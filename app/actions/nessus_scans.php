<?php
require_once($approot . 'lib/class.Config.php');

include_once($templates. 'admin_headers.tpl.php');
require_once($approot . 'lib/class.Form.php');
require_once($approot . 'lib/class.Nessus.php');
include_once($templates . 'nessus.tpl.php');

$form = new Form();
$formname = 'nessus_scan';
$form->set_name($formname);
$token = $form->get_token();
$form->save();

    $api = new Nessus(
        $__url = 'https://probe.security.isc.upenn.edu',
        $__port = '8834',
        $__username = "jamed",
        $__password = ""
    );
    
    $policy = $api->policy_list();
    print_r($policy);
    
//	$uuid = "d77aaa00-0722-1b8a-2e01-d8c4d0f25c4d6f64bcf0a15d625a";
//	$hostname = "coop.sas.upenn.edu";
//	
//	$ports = $api->report_ports($uuid, $hostname);
//	print_r($ports["protocol"]);
	
	echo "<br /><br /><br /><br />";
	
//	foreach ($ports["portList"] as $key => $p) {
//		$port_values = array(
//			"port" => $key,
//			"protocol" => $p["protocol"]
//		);
//		current($port_values);
//		next($port_values);
//		print_r($port_values);
//		
//	}
	
//	foreach ($uuid_values as $key => $u) {
//		$hosts = $api->report_hosts($u);
//		$host_uuid = array(
//			"uuid" => $u,
//			"hostname" => $hosts["hostList"]["hostname"]
//		);
//		print_r($host_uuid);
//	}
	
	$reports = $api->report_list();
	$uuid_values = array();
	foreach ($reports["reports"] as $response) {
		$uuid_values[] = $response["name"];
		current($uuid_values);
		next($uuid_values);
	}
	var_dump($uuid_values);
	
	echo "<br /><br /><br /><br />";
	
	// Get array with uuid, hostname, port, and protocol for every uuid
	foreach ($uuid_values as $key => $u) {
		$hosts = $api->report_hosts($u);
		$host_uuid = array(
			"uuid" => $u,
			"hostname" => $hosts["hostList"]["hostname"]
		);
		//print_r($host_uuid["uuid"]);
		$ports = $api->report_ports($host_uuid["uuid"], $host_uuid["hostname"]);
		foreach ($ports["portList"] as $key => $p) {
			$port_values = array(
				"uuid" => $host_uuid["uuid"],
				"hostname" => $host_uuid["hostname"],
				"port" => $key,
				"protocol" => $p["protocol"]
			);
			//print_r($port_values);
			echo "<br /><br />";
			$details = $api->report_details($port_values["uuid"], $port_values["hostname"], $port_values["port"], $port_values["protocol"]);
			//print_r($details);
			foreach ($details["portDetails"] as $keyd => $d) {
				$details_values = array(
					"uuid" => $u,
					"hostname" => $hosts["hostList"]["hostname"],
					"port" => $key,
					"protocol" => $p["protocol"],
					"plugin id" => $keyd,
					"plugin name" => $d["pluginName"],
					"severity" => $d["severity"],
					"solution" => $d["data"]["solution"],
					"description" => $d["data"]["description"],
					"risk factor" => $d["data"]["risk_factor"],
					"synopsis" => $d["data"]["synopsis"],
					"plugin output" => $d["data"]["plugin_output"]
					//"plugin version" => $d["data"]["plugin_version"]
				);
				echo "<pre>";
				print_r($details_values);
				echo "</pre>";
			}
		}
	}
?>