<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Nmap_result.php');


class TestOfNmap_resultClass extends UnitTestCase {
	
	function setUp() {
		$this->nr = new Nmap_result();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->nr, 'Nmap_result');
	}
	
	function testId() {
		$this->assertEqual($this->nr->get_id(), 0);
	}
	
	function testGetDetails() {
		$this->assertIsA($this->nr->get_details(), 'String');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->nr->get_collection_definition(), 'String');
	}
	
	function testHostId() {
		$this->nr->set_host_id(-1);
		$this->assertEqual($this->nr->get_host_id(), -1);
	}
	
	function testPortNumber() {
		$this->assertTrue($this->nr->set_port_number(80));
		$this->assertEqual($this->nr->get_port_number(), 80);
		$this->assertFalse($this->nr->set_port_number(-1));
		$this->assertFalse($this->nr->set_port_number(65536));
	}
}
?>