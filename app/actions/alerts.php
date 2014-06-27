<?php
/**
 * This is the default subcontroller for displaying system
 * alerts
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Include the Alert class
 */
require_once($approot . 'lib/class.Alert.php');
require_once($approot . 'lib/class.Collection.php');

// Allow both GET and POST methods
if (isset($_POST['startdate']) && $_POST['startdate'] !== '') $_GET['startdate'] = $_POST['startdate'];
if (isset($_POST['enddate']) && $_POST['enddate'] !== '') $_GET['enddate'] = $_POST['enddate'];
if (isset($_POST['ip']) && $_POST['ip'] !== '') $_GET['ip'] = $_POST['ip'];

$startdateplaceholder = isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : '0000-00-00';
$enddateplaceholder = isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : '0000-00-00';
$ipplaceholder = isset($_GET['ip']) ? htmlentities($_GET['ip']) : '0.0.0.0';

$filter = array();
if (isset($_GET['ip'])) $filter['ip'] = $_GET['ip'];
if (isset($_GET['startdate'])) $filter['startdate'] = $_GET['startdate'];
if (isset($_GET['enddate'])) $filter['enddate'] = $_GET['enddate'];
$collection = new Collection('Alert', $filter, 'get_collection_by_dates_ip');

$alerts = array();
if (is_array($collection->members)) $alerts = $collection->members;

// Necessary includes for filter form
require_once($approot . 'actions/global.php');
require_once($approot . 'lib/class.Form.php');
$filter_form = new Form();
$filter_form->set_name('alert_filter_form');
$filter_form_token = $filter_form->get_token();
$filter_form->save();

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'alerts.tpl.php');

?>