<?php
/**
 * interface.Maleable_Object_Interface.php
 * 
 * @package HECTOR
 */
 
/**
 * interface.Maleable_Object_Interface.php
 *
 * @abstract This interface is intended to allow for standard API throughout the application.
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
interface Maleable_Object_Interface {
	public function delete();
	public function get_add_alter_form();
	public function get_collection_definition($filter = '', $orderby = '');
	public function get_displays();
	public function get_id();
	public function process_form($callback, $value);
	public function save();
}
?>