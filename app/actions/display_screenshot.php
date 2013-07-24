<?php
/**
 * Display screenshot
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @version 2013.7.18
 */
require_once($approot . 'lib/class.Db.php');
$approot = getcwd() . '/../app/';
if (!(isset($_GET['url']) and isset($_GET['ajax']))) {
	header("HTTP/1.1 404 Not Found");
	print "Error 404 Not Found";
}
else {
	$db = Db::get_instance();
	$sql = array('select url_screenshot from url where url_url=\'?s\'',$_GET['url']);
	$result = $db->fetch_object_array($sql);
	if ($result[0]->url_screenshot != null) {
		$filename = $result[0]->url_screenshot;
		if (file_exists($approot . 'screenshots/' . $filename)) {
			$imginfo = getimagesize($approot . 'screenshots/' . $filename);
			header("Content-type: ". $imginfo['mime']);
			readfile($approot . 'screenshots/' . $filename);
		}
		else { 
			header("HTTP/1.1 404 Not Found");
			print "Error 404 Not Found";
			$db = Db::get_instance();
			$sql = array('update url set url_screenshot=NULL where url_url=\'?s\'',$_GET['url']);
			$db->iud_sql($sql);
			}
	}
	else { 
		header("HTTP/1.1 404 Not Found");
		print "Error 404 Not Found";
	}
	$db->close();
}
?>