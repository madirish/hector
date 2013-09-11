<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Scan_type.php');


class TestOfScan_typeClass extends UnitTestCase {
	
	function setup() {
		global $approot;
		$approot = '/opt/hector/app';
		$this->scan_type = new Scan_type();
	}
	
	function tearDown() {
		$this->scan_type->delete();
	}
	
	function testScan_typeClass() {
		$this->assertIsA($this->scan_type, 'Scan_type');
	}
	
	function testScan_typeId() {
		$this->assertEqual($this->scan_type->get_id(), 0);
	}
	
	function testScan_typeName() {
		$name = 'Test';
		$this->scan_type->set_name($name);
		$this->assertEqual($this->scan_type->get_name(), $name);
	}
	
	function testScan_typeFlags() {
		$flags = 'Test';
		$this->scan_type->set_flags($flags);
		$this->assertEqual($this->scan_type->get_flags(), $flags);
	}
	
	function testScan_typeScript() {
		$script = 'Test';
		$this->scan_type->set_script($script);
		$this->assertEqual($this->scan_type->get_script(), $script);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->scan_type->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->scan_type->get_collection_definition(), 'String');
	}
	
	function testGetDisplays() {
		$this->assertIsA($this->scan_type->get_displays(), 'Array');
	}
	
	function testSaveDelete() {
		$this->scan_type->set_name('Test');
		$this->assertTrue($this->scan_type->save());
		$this->assertTrue($this->scan_type->get_id() > 0 );
		$this->assertTrue($this->scan_type->delete());
	}
}
?>