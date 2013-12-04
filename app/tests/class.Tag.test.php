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
		$this->assertEqual($this->tag->get_id(), 0);
	}
	
	function testTagName() {
		$name = 'Test';
		$this->tag->set_name($name);
		$this->assertEqual($this->tag->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->tag->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->tag->get_collection_definition(), 'String');
	}
	
	function testGetDisplays() {
		$this->assertIsA($this->tag->get_displays(), 'Array');
	}
	
	function testSaveDelete() {
		$this->tag->set_name('Test');
		$this->assertTrue($this->tag->save());
		$id = $this->tag->get_id();
		$this->assertTrue($id > 0 );
		$newTag = new Tag($id);
		$this->assertTrue($newTag->getName == 'Test');
		$this->assertTrue($newtag->delete());
	}
}
?>