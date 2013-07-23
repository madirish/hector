<?php
/**
 * Display screenshot
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @version 2013.7.18
 */
require_once($approot . 'lib/class.Db.php');
$approot = getcwd() . '/../app/';
if (!(isset($_GET['url']) and isset($_GET['ajax']))) {
	header("HTTP/1.0 404 Not Found");
	print "Error 404 Not Found";
}
else {
	$db = Db::get_instance();
	$sql = array('select url_screenshot from url where url_url=\'?s\'',$_GET['url']);
	$result = $db->fetch_object_array($sql);
	$db->close();
	if ($result[0]->url_screenshot != null) {
		$filename = $result[0]->url_screenshot;
		if (file_exists($approot . 'screenshots/' . $filename)) {
			$imginfo = getimagesize($approot . 'screenshots/' . $filename);
			header("Content-type: ". $imginfo['mime']);
			readfile($approot . 'screenshots/' . $filename);
		}
		else { 
			header("HTTP/1.0 404 Not Found");
			print "Error 404 Not Found";
			}
	}
	else { 
		header("HTTP/1.0 404 Not Found");
		print "Error 404 Not Found";
	}
}
?>