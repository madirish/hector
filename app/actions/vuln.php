<?php
/**
 * This is the default subcontroller for vulnerability
 * reports
 * 
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.29
 * 
 */

/**
 * Require the factory class
 */
require_once($approot . 'lib/class.Collection.php');
$vuln_details = new Collection('Vuln_detail');

include_once($templates . 'admin_headers.tpl.php');
include_once($templates . 'vuln.tpl.php');
?>