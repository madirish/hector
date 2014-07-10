<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.User.php');


class TestOfUserClass extends UnitTestCase {
	
	function setUp() {
		$this->user = new User();
	}
	
	function tearDown() {
		$this->user->delete();
	}
	
	function testUserClass() {
		$this->assertIsA($this->user, 'User');
	}
	
	function testId() {
		$this->assertEqual($this->user->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->user->set_name($name);
		$this->assertEqual($this->user->get_name(), $name);
        $this->assertTrue($this->user->save());
        $id = $this->user->get_id();
        $user2 = new User();
        $user2->get_by_name($name);
        $this->assertEqual($id, $user2->get_id());
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->user->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->user->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->user->set_name('Test');
		$this->user->set_password('test');
		$this->assertTrue($this->user->save());
		$this->assertTrue($this->user->get_id() > 0 );
		$this->assertTrue($this->user->delete());
	}
	
	function testValidate() {
		$uname = 'Test';
		$pass = 'test';
		$this->user->set_name($uname);
		$this->user->set_password($pass);
		$this->assertTrue($this->user->save());
		$this->assertTrue($this->user->validate($uname,$pass));
		$this->assertFalse($this->user->validate($uname,'NotThePasswordForThisUser'));
	}
	
    function testSupportGroupIDs() {
    	$sgids = array(1,2,3,4);
        $this->assertTrue($this->user->set_supportgroup_ids($sgids));
        $this->assertEqual($sgids, $this->user->get_supportgroup_ids());
        $sgids[] = 'bad';
        $this->assertFalse($this->user->set_supportgroup_ids($sgids));
        $this->assertNotEqual($sgids, $this->user->get_supportgroup_ids());
    }
    
    function testIsAdmin() {
    	$this->assertTrue($this->user->set_is_admin(1));
        $this->assertEqual(true, $this->user->get_is_admin());
        $this->assertEqual('Yes', $this->user->get_is_admin_readable());
        $this->assertTrue($this->user->set_is_admin(0));
        $this->assertEqual('No', $this->user->get_is_admin_readable());
    }
    

	function testGetHostIds() {
		$sgids = 1234;
		$this->assertIsA($this->user->get_supportgroup_ids(), 'Array');
		$this->assertFalse($this->user->set_add_supportgroup_id(0));
		$this->assertTrue($this->user->set_add_supportgroup_id(1234));
		$this->assertTrue(count($this->user->get_supportgroup_ids()) == 1);
		$ids = $this->user->get_supportgroup_ids();
		$this->assertTrue($ids[0] == $sgids);
	}
}
?>