<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Scan.php');


class TestOfScanClass extends UnitTestCase {
	
	function setUp() {
		$this->scan = new Scan();
	}
	function tearDown() {
		$this->scan->delete();
	}
	
	function testScanClass() {
		$this->assertIsA($this->scan, 'Scan');
	}
	
	function testId() {
		$this->assertEqual($this->scan->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->scan->set_name($name);
		$this->assertEqual($this->scan->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->scan->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->scan->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->scan->set_name('Test');
		$this->assertTrue($this->scan->save());
		$this->assertTrue($this->scan->get_id() > 0 );
		$this->assertTrue($this->scan->delete());
	}
}
?>