<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Host_group.php');
require_once(dirname(__FILE__) . '/../lib/class.Host.php');


class TestOfHost_groupClass extends UnitTestCase {
	
	function setUp() {
		$this->host_group = new Host_group();
	}
	
	function tearDown() {
		$this->host_group->delete();
	}
	
	function testHost_groupClass() {
		$this->assertIsA($this->host_group, 'Host_group');
	}
	
	function testId() {
		$this->assertEqual($this->host_group->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->host_group->set_name($name);
		$this->assertEqual($this->host_group->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->host_group->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->host_group->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->host_group->set_name('Test');
		$this->assertTrue($this->host_group->save());
		$this->assertTrue($this->host_group->get_id() > 0 );
		$this->assertTrue($this->host_group->delete());
	}
    
    function testSetApplyToAll() {
        $newhost = new Host();
        $newhost->set_ip('127.0.0.1');
        $newhost->save();
    	$this->host_group->set_applytoall(1);
        $this->assertTrue(in_array($this->host_group->get_id(), $newhost->get_host_group_ids()));
        $newhost->delete();
    }
	
	function testAddDeleteHostFromGroup() {
		$this->host_group->set_name('Test');
		$this->host_group->save();
		$this->assertTrue($this->host_group->add_host_to_group(-1) );
		$hosts = $this->host_group->get_host_ids();
		$this->assertIsA($hosts, 'Array');
		$this->assertTrue(count($hosts) > 0);
		foreach ($hosts as $host) {
			$this->assertIsA($host, 'int');
		}
		$this->assertTrue($this->host_group->delete_host_from_group(-1));
		$hosts = $this->host_group->get_host_ids();
		$this->assertIsA($hosts, 'Array');
		$this->assertTrue(count($hosts) == 0);
		$this->assertTrue($this->host_group->delete());
	}
}
?>