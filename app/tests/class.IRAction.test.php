<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.IRAction.php');


class TestOfLocationClass extends UnitTestCase {
	
	function setUp() {
		$this->action = new IRAction();
	}
	
	function tearDown() {
		$this->action->delete();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->action, 'IRAction');
	}
	
	function testId() {
		$this->assertEqual($this->action->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->action->set_action($name);
		$this->assertEqual($this->action->get_action(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->action->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->action->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->action->set_action('Test');
		$this->assertTrue($this->action->save());
		$this->assertTrue($this->action->get_id() > 0 );
		$this->assertTrue($this->action->delete());
	}
}
?>