<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.IRDiscovery.php');


class TestOfLocationClass extends UnitTestCase {
	
	function setUp() {
		$this->discovery = new IRDiscovery();
	}
	
	function tearDown() {
		$this->discovery->delete();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->discovery, 'IRDiscovery');
	}
	
	function testId() {
		$this->assertEqual($this->discovery->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->discovery->set_method($name);
		$this->assertEqual($this->discovery->get_method(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->discovery->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->discovery->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->discovery->set_method('Test');
		$this->assertTrue($this->discovery->save());
		$this->assertTrue($this->discovery->get_id() > 0 );
		$this->assertTrue($this->discovery->delete());
	}
}
?>