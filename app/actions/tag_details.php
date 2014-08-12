<?php 
/**
 * Subcontroller to gather details for a specific tag
 * 
 * @package Hector
 * @author Ubani A Balogun
 * @version 2014.08.07
 */

require_once($approot . 'lib/class.Db.php');
include_once($approot . 'lib/class.Tag.php');
include_once($approot . 'lib/class.Incident.php');
include_once($approot . 'lib/class.Article.php');
include_once($approot . 'lib/class.Vuln.php');
include_once($approot . 'lib/class.Host.php');

// screenshots.css
$css = '';
$css .= "<link href='css/jquery.dataTables.css' rel='stylesheet'>\n";

// javascripts
$javascripts = '';
$javascripts .= "<script type='text/javascript' src='js/jquery.dataTables.min.js'></script>\n";
$javascripts .= "<script type='text/javascript' src='js/tag_details.js'></script>\n";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tag = new Tag($id);
$tag_name = $tag->get_name();



$incident_ids = $tag->get_incident_ids();

$incidents = array();

if (isset($incident_ids[0])){
	foreach ($incident_ids as $incident_id){
		$incident = new Incident($incident_id);
		$incidents[] = $incident->get_object_as_array();
	}
}

$article_ids = $tag->get_article_ids();

$articles = array();

if (isset($article_ids[0])){
	foreach ($article_ids as $article_id){
		$article = new Article($article_id);
		$articles[] = $article->get_object_as_array();
	}
}


$vuln_ids = $tag->get_vuln_ids();

$vulns = array();
if (isset($vuln_ids[0])){
	foreach ($vuln_ids as $vuln_id){
		$vuln = new Vuln($vuln_id);
		$vulns[] = $vuln->get_object_as_array();
	}
}

$host_ids = $tag->get_host_ids();

$hosts = array();
if (isset($host_ids[0])){
	foreach ($host_ids as $host_id){
		$host = new Host($host_id);
		$hosts[] = $host->get_object_as_array();
	}
}

$tag_weights = array();
$tag_weights['Incidents'] = count($incident_ids);
$tag_weights['Articles'] = count($article_ids);
$tag_weights['Vulnerabilities'] = count($vuln_ids);
$tag_weights['Hosts'] = count($host_ids);
arsort($tag_weights);



include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'tag_details.tpl.php');

?>