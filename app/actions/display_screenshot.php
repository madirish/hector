<?php
/**
 * Display screenshot
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @version 2014.7.28
 * @package HECTOR
 */

/**
 * Require the database
 */
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Url.php');
$approot = getcwd() . '/../app/';

if (!(isset($_GET['url']) and isset($_GET['ajax']))){
	header("HTTP/1.1 404 Not Found");
	print "Error 404 Not Found";
}else{
	$url = new Url($param=$_GET['url'],$type='url');
	$screenshot = $url->get_screenshot();
	
	if ($screenshot != null){
		$filename = $screenshot;
		if (file_exists($approot . 'screenshots/' . $filename )){
			$imginfo = getimagesize($approot . 'screenshots/' . $filename);
			header("Content-type: " . $imginfo['mime']);
			readfile($approot . 'screenshots/' . $filename);
		}
	}
	else {
		header("HTTP/1.1 404 Not Found");
		print "Error 404 Not Found";
	}	
}
?>