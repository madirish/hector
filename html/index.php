<?php
/**
 * This is the controller, all rivers flow from this source
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

//Start up a unique session
session_name('HECTOR');
session_start();
// Global variables
$approot = getcwd() . '/../app/';
$templates = $approot . 'templates/';
$jsroot = getcwd() . '/js';
$javascripts = '';
$testscripts = array();
$testcss = array();

/**
 * Include the Configs
 */
require_once($approot . 'lib/class.Config.php');
/**
 * Require the User object for access and permissions
 */
require_once($approot . 'lib/class.User.php');
new Config();

// CoSign integration (fall back to regular auth if this fails)
if ( isset( $_SERVER['REMOTE_USER'] ) && !empty( $_SERVER['REMOTE_USER'] )) {
	// REMOTE_USER received, however auth could have been via HTTP Basic or Digest; we check further below
	if ( $_SERVER['AUTH_TYPE'] == 'Cosign' && isset( $_SERVER['COSIGN_SERVICE'] ) ) {
		//PHP replaces '.' with '_' in $_COOKIE array keys, so we do the same in order to index the CoSign service cookie
		$service_name = preg_replace('/\./', '_', $_SERVER['COSIGN_SERVICE']);
		if ( isset( $_COOKIE[$service_name] ) ) {
			// Authentication was successful
			$user = new User();
			$user->get_by_name($_SERVER['REMOTE_USER']);
			$_SESSION['user_id'] = $user->get_id();
		}
	}
}

/** 
 * Form prepocessor (protect against XSRF)
 */
if (isset($_POST) && count($_POST) > 0) {
	include_once($approot . 'actions/form_preprocess.php');
}

/**
 * Begin program flow control.  Build an array
 * of valid actions so we can hand off control 
 * from the GET variable without having to
 * worry about null byte injection.
 * Ref:  http://www.madirish.net/?article=436
 */
$actions = array();
if (! $files = opendir($approot . '/actions')) {
	die("Error opening actions directory.  Please contact a system administrator.");
}
while (($dir = readdir($files)) !== false) {
	if (substr($dir, -4) == ".php") $actions[] = substr($dir, 0, -4);
}

// Set up the default action
$action = (isset($_GET['action']) && $_GET['action'] == 'login_scr') ? 'login_scr' : 'login';
$appuser = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != null) {
	$action = 'summary';
	$appuser = new User($_SESSION['user_id']);
	if (isset($_GET['action']) && in_array($_GET['action'], $actions)) {
		$action = $_GET['action'];
		// require admin roles for some actions
		$obj = isset($_GET['object']) ? $_GET['object'] : '';
		if (in_array(strtolower($obj), array('host_group','users','user','scan','scan_type')) && ! $appuser->get_is_admin()) {
			$action = 'admin_only';
		}
	}
}

// Set the HTML 5 Content Security Policy and Reporting
/*$proto = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
$policy = ' default-src ' . $proto . $_SERVER['SERVER_NAME'] . ' \'self\';';
$policy .= ' img-src \'self\' http://www.sas.upenn.edu http://www.upenn.edu;';
$policy .= ' frame-src \'none\';';
$policy .= ' object-src \'none\';';
$policy .= ' style-src \'self\';';
$policy .= ' script-src \'self\' \'unsafe-eval\';';
$policy .= ' report-uri /hector/?action=csp-report;';
header("Content-Security-Policy: $policy");*/
//header("X-Content-Security-Policy: $policy");

/**
 * Hand off to subcontrollers
 */
if ($action == 'csp-report') {
	include_once($approot . 'actions/csp-report.php');
}
else {
	if ( (! isset($_SESSION['user_id']) || 
			$_SESSION['user_id'] == null || 
			$action == 'logout') ) {
		// User isn't logged in, use static header template
		if ($action !== 'login_scr') {
			include_once($templates . 'header.tpl.php');
		}
		
	}
	else {
		// Necessary includes for search form
		require_once($approot . 'actions/global.php');
		require_once($approot . 'lib/class.Form.php');
		$ip_search = new Form();
		$ip_search_name = 'search_ip_form';
		$ip_search->set_name($ip_search_name);
		$ip_search_token = $ip_search->get_token();
		$ip_search->save();
	}
	include_once($approot . 'actions/' . $action . '.php');
	if (! isset($_GET['ajax'])) include_once($templates . 'footer.tpl.php');
}
?>
