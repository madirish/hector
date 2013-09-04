<?php
/**
 * Include the administrative headers along with additional
 * navigation elements if the user is an admin level user.
 * 
 * @package HECTOR
 */
 
/**
 * Include the regular headers
 */
include_once($templates. 'admin_headers.tpl.php');

/**
 * Include any special admin headers
 */
include_once($templates . 'admin_only.tpl.php');
?>