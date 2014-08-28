<?php 
/**
 * Global functions available to all actions pages
 * 
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @package HECTOR
 */



/**
 * Add a js file to the header of a page (admin_headers.tpl.php)
 * 
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @param String $filename The name of the js file to add
 * @return Boolean true on success, false on failure
 */
function hector_add_js($filename){
	global $testscripts;
	$testscripts = array();
	if ($filename != '' && in_hector_jsroot($filename)){
		$script = "<script type='text/javascript' src='js/$filename'></script>";
		if (!in_array($script, $testscripts)){
			$testscripts[] = $script;
		}
		return true;
	}
	return false;
}


/**
 * Checks if a javascript file is in HECTOR's javascript directory
 * 
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @param String $filename The javascript file to check for
 * @return Boolean true if file exists in HECTOR's js root. false otherwise
 */

function in_hector_jsroot($filename){
	$jsroot = $_SESSION['approot'] . 'html/js';
	if ($filename !=''){
		$filepath = $jsroot . '/' . $filename;
		$exists = file_exists($filepath);
		$in_root = ($jsroot == dirname($filepath));
		if ($exists && $in_root){
			return true;
		}	
	}
	return false;
}

/**
 * Checks if a css file is in HECTOR's css directory
 * 
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @param String $filename The css file to check
 * @return Boolean true if file exsts in HECTOR's css directory. false otherwise
 */
function in_hector_cssroot($filename){
	$cssroot = $_SESSION['approot'] . 'html/css';
	if ($filename !=''){
		$filepath = $cssroot . "/$filename";
		$exists = file_exists($filepath);
		$in_root = ($cssroot == dirname($filepath));
		if ($exists && $in_root){
			return true;
		}
	}
	return false;
}


/**
 * Add a css file to the header of a page 
 * 
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @param String $filename The name of the css file to add
 * @return Boolean true on success, false on failure
 */
function hector_add_css($filename){
	global $testcss;
	$testcss = array();
	if ($filename != '' && in_hector_cssroot($filename)){
		$link = "<link href='css/$filename' rel='stylesheet'>";
		if (!in_array($link, $testcss)){
			$testcss[] = $link;
		}
		return true;
	}
	return false;
	
}

?>