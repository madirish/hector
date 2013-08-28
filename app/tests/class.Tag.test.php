<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Tag.php');


class TestOfTagClass extends UnitTestCase {
	
	function testTagClass() {
		$tag = new Tag();
		$this->assertIsA($tag, 'Tag');
	}
	
	function testTagId() {
		$tag = new Tag();
		$this->assertEqual($tag->get_id(), 0);
	}
	
	function testTagName() {
		$tag = new Tag();
		$name = 'Test';
		$tag->set_name($name);
		$this->assertEqual($tag->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$tag = new Tag();
		$this->assertIsA($tag->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$tag = new Tag();
		$this->assertIsA($tag->get_collection_definition(), 'String');
	}
	
	function testGetDisplays() {
		$tag = new Tag();
		$this->assertIsA($tag->get_displays(), 'Array');
	}
	
	function testSaveDelete() {
		$tag = new Tag();
		$tag->set_name('Test');
		$this->assertTrue($tag->save());
		$this->assertTrue($tag->get_id() > 0 );
		$this->assertNull($tag->delete());
	}
}
?>