<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.IRAgent.php');


class TestOfLocationClass extends UnitTestCase {
	
	function setUp() {
		$this->agent = new IRAgent();
	}
	
	function tearDown() {
		$this->agent->delete();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->agent, 'IRAgent');
	}
	
	function testId() {
		$this->assertEqual($this->agent->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->agent->set_name($name);
		$this->assertEqual($this->agent->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->agent->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->agent->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->agent->set_name('Test');
		$this->assertTrue($this->agent->save());
		$this->assertTrue($this->agent->get_id() > 0 );
		$this->assertTrue($this->agent->delete());
	}
}
?>