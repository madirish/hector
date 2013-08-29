<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Supportgroup.php');


class TestOfSupportgroupClass extends UnitTestCase {
	
	function setUp() {
		$this->supportgroup = new Supportgroup();
	}
	
	function tearDown() {
		$this->supportgroup->delete();
	}
	
	function testSupportgroupClass() {
		$this->assertIsA($this->supportgroup, 'Supportgroup');
	}
	
	function testId() {
		$this->assertEqual($this->supportgroup->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->supportgroup->set_name($name);
		$this->assertEqual($this->supportgroup->get_name(), $name);
	}
	
	function testEmail() {
		$email = 'test@example.com';
		$this->assertTrue($this->supportgroup->set_email($email));
		$this->assertEqual($this->supportgroup->get_email(), $email);
		$this->assertFalse($this->supportgroup->set_email('ImAnIllegalEmail.com@com#p'));
	}
	
	function testGetHostIds() {
		$this->assertIsA($this->supportgroup->get_host_ids(), 'Array');
	}
	
	function testGetDetails() {
		$this->assertIsA($this->supportgroup->get_details(), 'String');
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->supportgroup->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->supportgroup->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->supportgroup->set_name('Test');
		$this->supportgroup->set_email('test@example.com');
		$this->assertTrue($this->supportgroup->save());
		$this->assertTrue($this->supportgroup->get_id() > 0 );
		$this->assertTrue($this->supportgroup->delete());
		$this->assertTrue($this->supportgroup->get_id() == 0 );
	}
	
	function testDelete() {
		$this->assertFalse($this->supportgroup->delete());
	}
}
?>