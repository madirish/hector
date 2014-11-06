<?php
/**
 * This is the default subcontroller for the configuration
 * of various aspects of hector.
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Get rid of actions/config.php and instead create
 * specific subcontrollers for each action.
 */

/**
 * Setup defaults.
 */
$object = isset($_GET['object']) ? strtolower($_GET['object']) : '';

if ($object == 'supportgroup' ) {
	$_GET['object'] = 'supportgroup';
	$object_readable = "Support Group";
	$explaination = 'Support Groups are assigned to hosts and ' .
			'<a href="?action=config&object=users">user accounts</a> to provide ' .
			'access permissions.  Host group contact e-mail is used to send ' .
			'reports about group hosts.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'host_group') {
	$_GET['object'] = 'host_group';
	$object_readable = "Host Group";
	$explaination = 'Host groups are sets of machines.  Host groups are used to ' .
			'target <a href="?action=config&object=scan">scheduled</a> scans as ' .
			'well as for access permissions for ' .
			'<a href="?action=config&object=supportgroup">support groups</a>.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'scan') {
	$_GET['object'] = 'scan';
	$object_readable = 'Scan Schedule';
	$explaination = 'The schedule allows <a href="?action=config&object=scan_type">' .
			'scripts</a> to be set to run at specific times for specific ' .
			'<a href="?action=config&object=host_group">host groups</a>.  A scheduled ' .
			'script is refered to as a scan.  For example, you could run an NMAP scan ' .
			'every day against critical servers, but only every week against ' .
			'workstations.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'scan_type') {
	$_GET['object'] = 'scan_type'; 
	$object_readable = 'Script Configuration';
	$explaination = 'Scripts are executables that can be configured with different ' .
			'arguments.  A configuration is not used until it is ' .
			'<a href="?action=config&object=scan">scheduled</a>.  For example, you can ' .
			'create an NMAP scan for TCP port 80 (HTTP), and another that searches for ' .
			'TCP port 25 (SMTP) and schedule them to run at different times.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'reports') {
	include_once($approot . 'actions/reports.php');
}
elseif ($object == 'users' || $object=='user') {
	$_GET['object'] = 'user';
	$object_readable = 'User';
	$explaination = 'User accounts are used for password based authentication.  ' .
			'However, access is determined by ' .
			'<a href="?action=config&object=supportgroup">support groups</a> to which ' .
			'the user belongs.  Admin users can add other users, as well as view ' .
			'and query all data in the HECTOR database.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'tags' || $object=='tag') {
	$_GET['object'] = 'tag';
	$object_readable = 'Tag';
	$explaination = 'Tags are free taxonomies that allow HECTOR system users to ' .
			'categorize information and draw correlations.  Tags can be applied to hosts ' .
			'vulnerabilities, articles and malware.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'location') {
	$_GET['object'] = 'location';
	$object_readable = 'Location';
	$explaination = 'Locations are used to track hosts to their physical address.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'feeds' || $object=='feed') {
	$_GET['object'] = 'feed';
	$object_readable = 'RSS feed';
	$explaination = 'RSS feeds can be used to automatically ingest open source ' .
			'intelligence into HECTOR for later analysis and correlation.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'add_hosts') {
	$_GET['object'] = 'add_hosts';
	include_once($approot . 'actions/add_hosts.php');
}
elseif ($object == 'api_key') {
	$_GET['object'] = 'api_key';
	$object_readable = 'API key';
	$explaination = 'API keys can be used to gain access to various feeds from ' .
			'within HECTOR such as the CIF format Kojoney2 activity report.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'risk') {
	$_GET['object'] = 'risk';
	$object_readable = 'Risk levels';
	$explaination = 'Risk levels are associated with vulnerability details for ' .
			'the purposes of classification and reporting.';
	include_once($approot . 'actions/details.php');
}
elseif ($object == 'vuln') {
	$_GET['object'] = 'vuln';
	$object_readable = 'Vulnerability class';
	$explaination = 'Vulnerability classes are super categories of the types of ' .
			'problems that might face a system or can be detected by a scan.  The ' .
			'specific instance of a vulnerability is a <a href="?action=vuln">' .
			'vulnerability detail</a>.';
	include_once($approot . 'actions/details.php');
}
else {
	include_once($templates. 'admin_headers.tpl.php');
	include_once($templates . 'config.tpl.php');
}


?>