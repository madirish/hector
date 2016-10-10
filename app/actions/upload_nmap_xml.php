<?php 
/**
 * Upload an XML file from an NMAP scan
 * 
 * @author Justin Klein Keane <justin@madirish.net>
 * @package HECTOR
 */

// Required includes
include_once($approot . 'lib/class.Config.php');
include_once($approot . 'lib/class.Db.php');
include_once($approot . 'lib/class.Dblog.php');

/**
 * Singletons
 */
new Config();
$db = Db::get_instance();
$dblog = Dblog::get_instance();
$log = Log::get_instance();

if (! function_exists("loggit")) {
	/**
	 * This function may not be instantiated if the script is
	 * called at the command line.
	 *
	 * @ignore Don't document this duplicate function.
	 */
	function loggit($status, $message) {
		global $log;
		global $dblog;
		$log->write_message($message);
		$dblog->log($status, $message);
	}
}

// Handle the XML file
if (isset($_FILES['nmap_xml'])) {
	loggit("NMAP upload XML process", "NMAP XML file uploaded started.");
    if ($_FILES['nmap_xml']['error'] == 0) {
        
    	$target_dir = $_SESSION['approot'] . "app/scripts/nmap_scan/";
    	$target_file = $target_dir . "nmap.xml." . time() . ".toimport";
        $nmap_file = $_FILES['nmap_xml']['tmp_name'];
        
    	if (move_uploaded_file($nmap_file, $target_file)) {
    		loggit("NMAP upload XML process", "NMAP XML file uploaded successfully.");
    		$message = 'NMAP XML file imported successfully! A scheduled job will process the file in < 5 minutes.';
    	}   
	}
    else {
	   	loggit("NMAP upload XML process", "There was a problem uploading the NMAP XML file.");
	   	$errors = array(
	        0=>"There is no error, the file uploaded with success",
	        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
	        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
	        3=>"The uploaded file was only partially uploaded",
	        4=>"No file was uploaded",
	        6=>"Missing a temporary folder"
		); 
	   	$message = 'There was a problem with the import! ' . $errors[$_FILES['nmap_xml']['error']];
    }    
}

$form_name='upload_nmap_xml';
$form = new Form();
$form->set_name($form_name);
$token = $form->get_token();
$form->save();


include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'upload_nmap_xml.tpl.php');

?>