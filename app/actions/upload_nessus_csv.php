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
    	$errors = array(
	        0=>"There is no error, the file uploaded with success",
	        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
	        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
	        3=>"The uploaded file was only partially uploaded",
	        4=>"No file was uploaded",
	        6=>"Missing a temporary folder"
		); 
    	$message = 'There was a problem with the import! ' . $errors[$_FILES['nessus_csv']['error']];
    }
}

$form_name='upload_nessus_csv';
$form = new Form();
$form->set_name($form_name);
$token = $form->get_token();
$form->save();


hector_add_js('bootstrap-datepicker.js');
hector_add_css('datepicker.css');

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'upload_nessus_csv.tpl.php');

?>