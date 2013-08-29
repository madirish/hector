<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Maleable_Object.php');


class TestOfMaleable_ObjectClass extends UnitTestCase {
	
	function setUp() {
		$this->maleable_object = new Maleable_Object();
	}
	
	function testMaleable_ObjectClass() {
		$this->assertIsA($this->maleable_object, 'Maleable_Object');
	}

	function testId() {
		$this->assertEqual($this->maleable_object->get_id(), 0);
	}
}
?>