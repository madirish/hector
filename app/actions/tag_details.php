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
include_once($approot . 'lib/class.Collection.php');

// screenshots.css
$css = '';

// javascripts
$javascripts = '';
hector_add_js('tag_details.js');

//$javascripts .= "<script type='text/javascript' src='js/tag_details.js'></script>\n";

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

$all_incidents = new Collection('Incident');

if (is_array($all_incidents->members) && isset($all_incidents->members[0])){
	$total_incidents = count($all_incidents->members);
	$tagged_incidents = count($incidents);
	$incidentpercent = round(($tagged_incidents / $total_incidents) * 100);
}else{
	$incidentpercent = 0;
}

$all_articles = new Collection('Article');

if (is_array($all_articles->members) && isset($all_articles->members[0])){
	$total_articles = count($all_articles->members);
	$tagged_articles = count($articles);
	$articlepercent = round(($tagged_articles/$total_articles) * 100);
}else{
	$articlepercent = 0;
}


$all_vulns = new Collection('Vuln');

if (is_array($all_vulns->members) && isset($all_vulns->members[0])){
	$total_vulns = count($all_vulns->members);
	$tagged_vulns = count($vulns);
	$vulnpercent = round(($tagged_vulns/$total_vulns) * 100);
}else{
	$vulnpercent = 0;
}


$all_hosts = new Host();
$total_hosts = $all_hosts->get_field_frequencies('host_id');

if (!empty($total_hosts)){
	$tagged_hosts = count($hosts);
	$hostpercent = round(($tagged_hosts/count($total_hosts)) * 100);
}else{
	$hostpercent = 0;
}

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'tag_details.tpl.php');

?>