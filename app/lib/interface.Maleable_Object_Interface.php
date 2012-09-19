<?php

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