<?php 
/**
 * Upload a CSV file from a Nessus report
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @todo Add exact time (currently midnight is set)
 */


// Handle the CSV upload
if (isset($_FILES['nessus_csv'])) {
    if ($_FILES['nessus_csv']['error'] == 0) {
        // Required includes
        include_once($approot . 'lib/class.Host.php');
        include_once($approot . 'lib/class.Vuln.php');
        include_once($approot . 'lib/class.Vuln_detail.php');
        include_once($approot . 'lib/class.Risk.php');

        // Helper function
        function strip_quotes($in) {
        	$retval = substr($in, 1, -1);
            return $retval;
        }
        $nessus_file = $_FILES['nessus_csv']['tmp_name'];
        //$scan_date = escapeshellarg($_POST['scan-date'] . '_00:00:00');
        $scan_date = $_POST['scan-date'];
        if (($handle = fopen($nessus_file, "rb")) !== FALSE) {
        while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Plugin ID,CVE,CVSS,Risk,Host,Protocol,Port,Name,Synopsis,Description,Solution,See Also,Plugin Output
            list($pluginid,$cve,$cvss,$risklevel,$host,$protocol,$port,$name,$synop,$desc,$sol,$seeAlso,$pluginout) = $line;
            // Ignore the header line
            if ($pluginid == 'Plugin ID') continue;
            // Don't import irrelevant info
            if ($risklevel == 'None') continue;
            
            $desc_string = '<div id="description">Description: ' . $synop . '</div>' . 
                            '<div id="cvss-score">CVSS: ' . $cvss . '</div>' . 
                            '<div id="solution">Solution: ' . $sol . '</div>';
                            
            $text_string = '<div id="protocol">Protocol: ' . $protocol . '</div>' . 
                            '<div id="port">Port: ' . $port . '</div>' . 
                            '<div id="detailed-explanation">' . $desc . '</div>' . 
                            '<div id="plugin-output">' . $pluginout . '</div>' . 
                            '<div id="references">' . $seeAlso . '</div>';
            
            $hostobj = new Host();
            $hostobj->set_ip($host); 
            $hostobj->lookup_by_ip();
            if ($hostobj->get_id() < 1) {
                $hostobj->set_name($host);
                $hostobj->save();
            }
            $vuln = new Vuln();
            $vuln->lookup_by_name_cve($name, $cve);
            if ($vuln->get_id() < 1) {
            	$vuln->set_cve($cve);
                $vuln->set_name($name);
                $vuln->set_description($desc_string);
                $vuln->save();
            }
            $vuln_detail = new Vuln_detail();
            $vuln_detail->lookup_by_vuln_id_host_id_date($vuln->get_id(),$hostobj->get_id(),$scan_date);
            if ($vuln_detail->get_id() < 1) {
                $risk = new Risk();
                $risk->lookup_by_name($risklevel);
                
            	$vuln_detail->set_host_id($hostobj->get_id());
                $vuln_detail->set_vuln_id($vuln->get_id());
                $vuln_detail->set_risk_id($risk->get_id());
                $vuln_detail->set_text($text_string);
                $vuln_detail->set_datetime($scan_date);
                $vuln_detail->save();
            }
         
        }}
        $message = 'File imported successfully!';
        //$cmd = 'python /opt/hector/app/scripts/nessus_csv_import.py -i ' . $nessus_file . ' -t ' . $scan_date;
        //$output = shell_exec($cmd);
        //$message = ($output == '') ? 'File successfully imported.' : htmlspecialchars($output);
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