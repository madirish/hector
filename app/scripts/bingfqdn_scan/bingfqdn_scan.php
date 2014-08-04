<?php

/**
 *   
 * Kick off a Bing API scan
 * 
 *   
 * Example usage:
 * 
 * $ php bingfqdn.php
 * 
 * This script is run from scan_cron.php 
 * 
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * 
 * Last modified July 31, 2014
 * 
 * @todo filter by host group
 */
 
if(php_sapi_name() == 'cli') {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $approot = realpath(substr($_SERVER['PATH_TRANSLATED'],0,strrpos($_SERVER['PATH_TRANSLATED'],'/')) . '/../../') . '/';  
}

/**
 * Neccesary includes
 */
require_once($approot . 'lib/class.Config.php');
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Dblog.php');
require_once($approot . 'lib/class.Log.php');

// Make sure of the environment
global $add_edit;
if(php_sapi_name() != 'cli') {
    // Error, we shouldn't use this script from the web interface
}
else {  
    // Set high mem limit to prevent resource exhaustion
    ini_set('memory_limit', '512M');    
    syslog(LOG_INFO, 'bingfqdn_scan.php starting.');      
    /**
     * Singletons
     */
    new Config();
    $db = Db::get_instance();
    $dblog = Dblog::get_instance();
    $log = Log::get_instance();
    $dblog->log("bingfqdn_scan.php status", "bingfqdn_scan.php invoked.");
    $log->write_message("bingfqdn_scan.php invoked.");
    $output = system($_SESSION['python_exec_path'] . ' ' . $approot . 'scripts/bingfqdn/bingfqdn.py', $retval);
    $dblog->log("bingfqdn.php process", "bingfqdn.py complete [return code was $retval]");
    $log->write_message("bingfqdn scan: bingfqdn.py complete");
    
    // Shut down nicely
    syslog(LOG_INFO, 'bingfqdn.php complete.');
}