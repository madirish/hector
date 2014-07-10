<?php
/**
 * class.BulkHostAdder.php
 *
 * @package HECTOR
 * @abstract This class provides a mechanism to perform bulk operations on hosts directly.
 * @author Justin C. Klein Keane <justin@madirish.net>
 */

/**
 * Required includes
 */
require_once("class.Config.php");
require_once("class.Log.php");
require_once("class.Db.php");

/**
 * This class provides the interface to the database.
 * @package HECTOR
 * @subpackage util
 *
 */
Class BulkHostAdder {
    
    private $error_message;
    
    
    /**
     * Add a list of consecutive IP addresses to the 
     * database as new host records
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The start IP listed in dot notation
     * @param String The end IP listed in dot notation
     * @param Array An arrayof hostgroups to assign the new hosts to
     * @return Boolean True on success or False on error.
     */
    public function add_by_IP($start_ip, $end_ip, $hostgroups = array()) {
        
        $db = Db::get_instance();
        $startip = ip2long($start_ip); 
        $endip = isset($end_ip) && $end_ip != '' ? ip2long($end_ip) : $startip;
        if ($endip < $startip) {
            $this->error_message = "Start IP must be less than end IP.";
            return false;
        }
        $ip = $startip;
        while ($ip <= $endip) {
            // Don't save 192.168.2.0 for instance
            // Probably a better mathy way to do this ($ip%8 == 0) ?
            $ipstring = long2ip($ip);
            if (substr(long2ip($ip), -2) != ".0") {
                // Check for duplicate entries
                $sql = array(
                    'SELECT host_id FROM host WHERE host_ip_numeric = inet_aton(\'?i\')',
                    $ipstring
                );  
                $result = $db->fetch_object_array($sql);
                $id = isset($result[0]->host_id) ? $result[0]->host_id : 0;
                
                // If the host is new add it
                if ($id < 1) {
                    $sql = array(
                        'INSERT INTO host SET host_ip = \'?s\', host_ip_numeric = inet_aton(\'?s\'), host_name = \'?s\'',
                        $ipstring, $ipstring, $ipstring
                    );
                    $db->iud_sql($sql);
                    // Now set the id
                    $id = mysql_insert_id();    
                    // Insert the host groups
                    foreach ($hostgroups as $group_id) {
                        $sql = array(
                            'INSERT INTO host_x_host_group SET host_id = ?i, host_group_id = ?i',
                            $id, $group_id
                        );
                        $db->iud_sql($sql);
                    }
                }
                // if this record already exists just set up the hostgruops
                else {
                    foreach ($hostgroups as $group_id) {
                        $sql = array(
                            'SELECT host_group_id FROM host_group WHERE host_group_id = ?i AND host_id = ?i',
                            $group_id, $id
                        );  
                        $result = $db->fetch_object_array($sql);
                        $host_group_id = isset($result[0]->host_group_id) ? $result[0]->host_group_id : 0;
                        if ($host_group_id < 1) {
                            $sql = array(
                                'INSERT INTO host_x_host_group SET host_id = ?i, host_group_id = ?i',
                                $id, $group_id
                            );
                            $db->iud_sql($sql);
                        }
                    }
                    
                }
            }
            $ip++; 
        }
    	
    }
    
    /**
     * Getter for any error message
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return String Any error message that's been set
     */
    public function get_error() {
        return $this->error_message;
    }
}