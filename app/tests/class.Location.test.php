<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Location.php');


class TestOfLocationClass extends UnitTestCase {
	
	function setUp() {
		$this->location = new Location();
	}
	
	function tearDown() {
		$this->location->delete();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->location, 'Location');
	}
	
	function testId() {
		$this->assertEqual($this->location->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->location->set_name($name);
		$this->assertEqual($this->location->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->location->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->location->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->location->set_name('Test');
		$this->assertTrue($this->location->save());
		$this->assertTrue($this->location->get_id() > 0 );
		$this->assertTrue($this->location->delete());
	}
	
	function testGetHostIds() {
		$this->assertIsA($this->location->get_host_ids(), 'Array');
	}
}
?>