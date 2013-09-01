<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Tag.php');


class TestOfTagClass extends UnitTestCase {
	
	function setup() {
		$this->tag = new Tag();
	}
	
	function tearDown() {
		$this->tag->delete();
	}
	
	function testTagClass() {
		$this->assertIsA($this->tag, 'Tag');
	}
	
	function testTagId() {
		$this->tag = new Tag();
		$this->assertEqual($this->tag->get_id(), 0);
	}
	
	function testTagName() {
		$this->tag = new Tag();
		$name = 'Test';
		$this->tag->set_name($name);
		$this->assertEqual($this->tag->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->tag = new Tag();
		$this->assertIsA($this->tag->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->tag = new Tag();
		$this->assertIsA($this->tag->get_collection_definition(), 'String');
	}
	
	function testGetDisplays() {
		$this->tag = new Tag();
		$this->assertIsA($this->tag->get_displays(), 'Array');
	}
	
	function testSaveDelete() {
		$this->tag = new Tag();
		$this->tag->set_name('Test');
		$this->assertTrue($this->tag->save());
		$this->assertTrue($this->tag->get_id() > 0 );
		$this->assertNull($this->tag->delete());
	}
}
?>