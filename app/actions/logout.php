<?php
/**
 * Log the user out of the system.
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upen.edu>
 * @package HECTOR
 */
 
/**
 * Setup defaults
 */
$_SESSION['user_id'] = '';
session_destroy();
include_once($templates . 'logout.tpl.php');
?>