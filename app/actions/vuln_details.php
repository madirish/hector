<?php
/**
 * This is the default subcontroller for vulnerability
 * details
 * 
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.29
 */

// Necessary includes
require_once($approot . 'lib/class.Vuln_detail.php');

$vuln_id = isset($_GET['id']) ? intval($_GET['id']) : '';
$vuln_details= new Vuln_detail($vuln_id);

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'vuln_details.tpl.php');

?> 