<?php 
/**
 * Upload a CSV file from a Nessus report
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Add timestamp field
 */

// Handle the CSV upload
if (isset($_FILES['nessus_csv'])) {
    if ($_FILES['nessus_csv']['error'] == 0) {
        $nessus_file = $_FILES['nessus_csv']['tmp_name'];
        shell_exec('python /opt/hector/app/scripts/nessus_csv_import.py -i ' . $nessus_file);
        $message = 'File successfully imported.';
    }
    else {
    	$message = 'There was a problem with the import!';
    }
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'upload_nessus_csv.tpl.php');

?>