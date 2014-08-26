<?php 
/**
 * Subcontroller for Deleting article objects
 * @author Ubani A Balogun <ubani@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Necessary Includes
 */
require_once($approot . 'lib/class.Article.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : '';

if ($id != ''){
	$target = new Article($id);
	$target->delete();
}
$message = 'Record deleted';
$_GET['id'] = null;
if (isset($_GET['ajax'])) {
	$ajax = true;
}
include_once($approot . 'actions/articles.php');
?>