<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Host.php');


class TestOfHostClass extends UnitTestCase {
	
	function setUp() {
		$this->host = new Host();
	}
	
	function tearDown() {
		$this->host->delete();
	}
	
	function testHostClass() {
		$this->assertIsA($this->host, 'Host');
	}
	
	function testId() {
		$this->assertEqual($this->host->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->host->set_name($name);
		$this->assertEqual($this->host->get_name(), 'test');
	}
	
	function testIP() {
		$ip = '127.0.0.1';
		$this->host->set_ip($ip);
		$this->assertEqual($this->host->get_ip(), $ip);
	}
	
	function testAltHostnames() {
		$althostname = 'foo.example.com';
		$this->host->set_alt_hostname($althostname);
		$altnames = $this->host->get_alt_hostnames();
		$this->assertIsA($altnames, 'Array');
		$this->assertEqual($altnames[0], $althostname);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->host->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->host->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->host->set_name('Test');
		$this->host->set_ip('10.255.255.250');
		$this->assertTrue($this->host->save());
		$this->assertTrue($this->host->get_id() > 0 );
		$this->assertTrue($this->host->delete());
	}
}
?>